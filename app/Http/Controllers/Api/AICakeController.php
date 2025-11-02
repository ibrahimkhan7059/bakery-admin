<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AICakeController extends Controller
{
    /**
     * Handle AI cake prediction request
     */
    public function predictCake(Request $request): JsonResponse
    {
        Log::info('🔵 AI predictCake method called', [
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_ip' => $request->ip(),
            'has_image' => $request->hasFile('image'),
            'content_type' => $request->header('Content-Type'),
            'user_agent' => $request->header('User-Agent')
        ]);

        try {
            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max
            ]);

            Log::info('✅ AI request validation passed');

            // Get the uploaded image
            $image = $request->file('image');
            
            Log::info('📁 Image file details', [
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'mime_type' => $image->getMimeType()
            ]);
            
            // Save image temporarily
            $tempPath = $image->store('temp/ai-uploads', 'public');
            $fullPath = Storage::disk('public')->path($tempPath);

            Log::info('💾 Image saved', [
                'temp_path' => $tempPath,
                'full_path' => $fullPath
            ]);

            // Call Python AI API
            $response = $this->callAIApi($fullPath);

            // Clean up temporary file
            Storage::disk('public')->delete($tempPath);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'AI prediction failed',
                    'error' => $response['error'] ?? 'unknown_error'
                ], 400);
            }

            // Get prediction results
            $prediction = $response['prediction'];
            $predictedCategory = $response['prediction']['category'];
            $isNotCake = ($predictedCategory === 'not_cake');
            
            // Check if disclaimer is present (non-cake image detected)
            $hasDisclaimer = isset($response['disclaimer']);

            // For non-cake images, return special response
            if ($isNotCake || $hasDisclaimer) {
                return response()->json([
                    'success' => true,
                    'prediction' => $prediction,
                    'is_cake' => false,
                    'message' => 'Please upload a cake image only.',
                    'matching_cakes' => []
                ]);
            }

            // Find matching cakes from Laravel database
            $matchingCakes = $this->findMatchingCakes($predictedCategory);

            return response()->json([
                'success' => true,
                'prediction' => $prediction,
                'is_cake' => true,
                'matching_cakes' => $matchingCakes,
                'message' => "Found " . count($matchingCakes) . " matching cakes in " . ucfirst(str_replace('_', ' ', $predictedCategory)) . " category"
            ]);

        } catch (\Exception $e) {
            Log::error('❌ AI Cake Prediction Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing image: ' . $e->getMessage(),
                'error' => 'processing_error'
            ], 500);
        }
    }

    /**
     * Call Python AI API
     */
    private function callAIApi(string $imagePath): array
    {
        try {
            $aiApiUrl = config('app.ai_api_url', 'http://10.130.8.2:5000') . '/predict_cake';
            
            Log::info('🔗 Calling AI API', [
                'ai_api_url' => $aiApiUrl,
                'image_path' => $imagePath,
                'file_exists' => file_exists($imagePath)
            ]);

            // Prepare the request to Python API
            $response = Http::timeout(60)->attach(
                'image',
                file_get_contents($imagePath),
                basename($imagePath)
            )->post($aiApiUrl);

            Log::info('📡 AI API Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                Log::info('✅ AI API call successful');
                return $response->json();
            }

            Log::error('❌ AI API call failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'AI API responded with error: ' . $response->status(),
                'error' => 'api_error'
            ];

        } catch (\Exception $e) {
            Log::error('❌ AI API Call Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to connect to AI service: ' . $e->getMessage(),
                'error' => 'connection_error'
            ];
        }
    }

    /**
     * Find matching cakes from database based on predicted category
     */
    private function findMatchingCakes(string $predictedCategory): array
    {
        try {
            // First, get all actual categories from the database
            $dbCategories = Category::pluck('name')->toArray();
            Log::info("All database categories: " . implode(', ', $dbCategories));
            
            // Only use cake-related categories (filter out non-cake categories)
            $cakeCategories = ['Birthday Cakes', 'Choclate Cakes', 'Mix Cakes', 'Wedding Cakes'];
            
            // Map AI categories to only cake categories
            $categoryMapping = [
                // Core AI categories mapped to only cake categories
                'cheesecake' => ['Birthday Cakes', 'Choclate Cakes', 'Mix Cakes', 'Wedding Cakes'],
                'chocolate' => ['Choclate Cakes', 'Birthday Cakes', 'Mix Cakes'],
                'red_velvet' => ['Birthday Cakes', 'Wedding Cakes', 'Mix Cakes'],
                'vanilla' => ['Birthday Cakes', 'Mix Cakes', 'Wedding Cakes'],
                'strawberry' => ['Birthday Cakes', 'Mix Cakes'],
                'black_forest' => ['Choclate Cakes', 'Birthday Cakes', 'Mix Cakes'],
                'fruit' => ['Mix Cakes', 'Birthday Cakes'],
                
                // Fallback category for all other predictions - only cake categories
                'extra_cakes' => $cakeCategories
            ];
            
            // Log the predicted category for debugging
            Log::info("AI predicted category: $predictedCategory");

            $searchTerms = $categoryMapping[$predictedCategory] ?? $dbCategories;
            
            // Log the search terms being used
            Log::info("Search terms for category '$predictedCategory': " . implode(', ', $searchTerms));
            
            // Check for available categories in the database first (for debugging)
            $availableCategories = Category::pluck('name')->toArray();
            Log::info("Available categories in database: " . implode(', ', $availableCategories));

            // Strategy: Get diverse products from multiple categories
            // First, try to get at least 2 products from each mapped category
            $matchingProducts = collect([]);
            $productsPerCategory = 2; // Get 2 products from each category
            
            Log::info("Searching for products in these categories: " . implode(', ', $searchTerms));
            
            foreach ($searchTerms as $categoryName) {
                // Additional safety check: only search in cake categories
                if (!in_array($categoryName, $cakeCategories)) {
                    Log::info("Skipping non-cake category: $categoryName");
                    continue;
                }
                
                $categoryProducts = Product::query()
                    ->whereHas('category', function ($categoryQuery) use ($categoryName) {
                        $categoryQuery->where('name', $categoryName);
                    })
                    ->with('category')
                    ->inRandomOrder() // Mix up the results
                    ->limit($productsPerCategory)
                    ->get();
                    
                Log::info("Found {$categoryProducts->count()} products in cake category '$categoryName'");
                $matchingProducts = $matchingProducts->concat($categoryProducts);
            }
            
            // If we have less than 8 products, fill with additional products from matched categories
            if ($matchingProducts->count() < 8) {
                $existingIds = $matchingProducts->pluck('id')->toArray();
                $needed = 8 - $matchingProducts->count();
                
                $additionalProducts = Product::query()
                    ->whereHas('category', function ($categoryQuery) use ($searchTerms) {
                        $categoryQuery->whereIn('name', $searchTerms);
                    })
                    ->whereNotIn('id', $existingIds)
                    ->with('category')
                    ->inRandomOrder()
                    ->limit($needed)
                    ->get();
                    
                Log::info("Adding {$additionalProducts->count()} additional products to reach 8 total");
                $matchingProducts = $matchingProducts->concat($additionalProducts);
            }
            
            // If still not enough, try broader search with name/description matching
            if ($matchingProducts->count() < 4) {
                Log::info("Still need more products, trying name/description search");
                
                // Create search terms from the predicted category
                $predictionTerms = explode('_', $predictedCategory);
                $existingIds = $matchingProducts->pluck('id')->toArray();
                $needed = 8 - $matchingProducts->count();
                
                $nameSearchProducts = Product::query()
                    ->where(function($q) use ($predictionTerms) {
                        // Search in product name
                        $q->where(function($nameQ) use ($predictionTerms) {
                            foreach ($predictionTerms as $term) {
                                if (strlen($term) > 2) { // Skip short words
                                    $nameQ->orWhere('name', 'like', '%' . $term . '%');
                                }
                            }
                        });
                        
                        // Or in description
                        $q->orWhere(function($descQ) use ($predictionTerms) {
                            foreach ($predictionTerms as $term) {
                                if (strlen($term) > 2) { // Skip short words
                                    $descQ->orWhere('description', 'like', '%' . $term . '%');
                                }
                            }
                        });
                    })
                    ->whereNotIn('id', $existingIds)
                    ->with('category')
                    ->limit($needed)
                    ->get();
                    
                Log::info("Name/description search found {$nameSearchProducts->count()} additional products");
                $matchingProducts = $matchingProducts->concat($nameSearchProducts);
            }
            
            // Take only first 8 products
            $matchingProducts = $matchingProducts->take(8);

            // Log for debugging
            Log::info("AI Search - Category: $predictedCategory, Found: " . $matchingProducts->count());
            $foundCategories = $matchingProducts->pluck('category.name')->unique()->toArray();
            Log::info("Final categories in results: " . implode(', ', $foundCategories));

            // If no products found with our strategy, fallback to broader search
            if ($matchingProducts->count() == 0) {
                Log::info("No products found with specific search for '$predictedCategory', trying broader search...");
                
                // Get products from only cake categories (not biscuits, breads, etc.)
                $cakeCategories = ['Birthday Cakes', 'Choclate Cakes', 'Mix Cakes', 'Wedding Cakes'];
                $matchingProducts = collect([]);
                $productsPerCategory = 2; // 2 products per category = 8 total
                
                foreach ($cakeCategories as $categoryName) {
                    $categoryProducts = Product::query()
                        ->whereHas('category', function ($categoryQuery) use ($categoryName) {
                            $categoryQuery->where('name', $categoryName);
                        })
                        ->with('category')
                        ->inRandomOrder()
                        ->limit($productsPerCategory)
                        ->get();
                        
                    Log::info("Fallback: Found {$categoryProducts->count()} products in cake category '$categoryName'");
                    $matchingProducts = $matchingProducts->concat($categoryProducts);
                }
                
                Log::info("Fallback search found: " . $matchingProducts->count() . " products from cake categories only");
            }

            // Format response for Flutter app
            $formattedCakes = [];
            foreach ($matchingProducts as $product) {
                // Log image path for debugging
                Log::info("Product ID {$product->id} image path: " . ($product->image ?? 'null'));
                
                $imageUrl = null;
                if ($product->image) {
                    // Use asset URL helper function instead
                    $imageUrl = asset('storage/' . $product->image);
                    
                    // Log for debugging
                    Log::info("Image URL for product {$product->id}: {$imageUrl}");
                }
                
                $formattedCakes[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'Cake',
                    'price' => (float) $product->price,
                    'price_formatted' => 'Rs. ' . number_format((float)$product->price, 0), // Pakistani Rupee format
                    'currency' => 'PKR',
                    'image_url' => $imageUrl,
                    'description' => !empty($product->description) ? $product->description : 'A delicious ' . $product->name . ' with premium ingredients',
                    'match_score' => $this->calculateMatchScore($product, $predictedCategory),
                    'laravel_product_id' => $product->id, // For cart integration
                    'available_quantity' => $product->quantity ?? 1,
                    'is_available' => true // All products are available
                ];
            }

            // Sort by match score (highest first)
            usort($formattedCakes, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });

            return $formattedCakes;

        } catch (\Exception $e) {
            Log::error('Error finding matching cakes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate match score based on product and predicted category
     */
    private function calculateMatchScore($product, string $predictedCategory): float
    {
        // Base score is now based on the specific category of the product
        $baseScore = 70.0;
        $categoryBoost = 0.0;
        
        // Get category of the product and predicted category name
        $productCategory = $product->category->name ?? '';
        $normalizedPredictedCategory = str_replace('_', ' ', $predictedCategory);
        $predictedCategoryWords = explode('_', $predictedCategory);
        
        // Assign priority based on the product's category
        switch ($productCategory) {
            case 'Special Cakes':
                if (in_array($predictedCategory, ['cheesecake', 'black_forest'])) {
                    $categoryBoost = 15.0;
                    Log::info("Premium match: Special Cakes category for {$predictedCategory}");
                } else {
                    $categoryBoost = 10.0;
                }
                break;
                
            case 'Birthday Cakes':
                if (in_array($predictedCategory, ['chocolate', 'strawberry', 'vanilla'])) {
                    $categoryBoost = 15.0;
                    Log::info("Premium match: Birthday Cakes category for {$predictedCategory}");
                } else {
                    $categoryBoost = 10.0;
                }
                break;
                
            case 'Cheese Cakes':
                if ($predictedCategory === 'cheesecake') {
                    $categoryBoost = 20.0; // Perfect match
                    Log::info("Perfect match: Cheese Cakes category for cheesecake");
                } else {
                    $categoryBoost = 5.0;
                }
                break;
                
            case 'Mix Cakes':
                // For mix cakes, focus more on name match than category
                $categoryBoost = 8.0;
                break;
                
            default:
                $categoryBoost = 5.0;
        }
        
        // Add the category boost
        $baseScore += $categoryBoost;
        
        // Boost score for name matches
        $productName = strtolower($product->name);
        $nameBoost = 0.0;
        
        // Check for exact predicted category in name
        if (str_contains($productName, $normalizedPredictedCategory)) {
            $nameBoost = 15.0;
            Log::info("Strong name match for product {$product->id}: {$product->name} contains {$normalizedPredictedCategory}");
        } else {
            // Check for individual words in name
            $wordMatchCount = 0;
            foreach ($predictedCategoryWords as $word) {
                if (strlen($word) > 2 && str_contains($productName, $word)) {
                    $wordMatchCount++;
                    Log::info("Word match in name: {$word} found in {$product->name}");
                }
            }
            
            if ($wordMatchCount > 0) {
                $nameBoost = 5.0 + ($wordMatchCount * 2.0);
                Log::info("Partial name matches for product {$product->id}: {$wordMatchCount} words matched");
            }
        }
        
        // Add name boost
        $baseScore += $nameBoost;
        
        // Small random variation for realistic ranking (smaller range for more consistency)
        $variation = rand(-2, 2);
        
        // Log the score calculation
        $finalScore = min(100.0, max(70.0, $baseScore + $variation));
        Log::info("Match score for product {$product->id} ({$product->name} in {$productCategory}): {$finalScore} (base: {$baseScore}, category boost: {$categoryBoost}, name boost: {$nameBoost})");
        
        return $finalScore;
    }

    /**
     * Get AI service health status
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->get(
                config('app.ai_api_url', 'http://10.130.8.2:5000') . '/health'
            );

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'ai_service' => 'healthy',
                    'message' => 'AI service is running',
                    'response' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'ai_service' => 'unhealthy',
                'message' => 'AI service is not responding',
                'status_code' => $response->status()
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'ai_service' => 'error',
                'message' => 'Failed to connect to AI service: ' . $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get available cake categories for reference
     */
    public function getCakeCategories(): JsonResponse
    {
        try {
            $categories = Category::where('is_active', true)
                                ->where(function ($query) {
                                    $query->where('name', 'like', '%cake%')
                                          ->orWhere('name', 'like', '%pastry%')
                                          ->orWhere('name', 'like', '%dessert%');
                                })
                                ->get(['id', 'name', 'description']);

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to check database products
     */
    public function debugProducts(): JsonResponse
    {
        try {
            $totalProducts = Product::count();
            
            // Get all categories
            $categories = Category::all(['id', 'name']);
            $categoryNames = $categories->pluck('name')->toArray();
            
            // Detailed products count by category
            $productsByCategory = [];
            foreach ($categories as $category) {
                // Count products
                $count = Product::where('category_id', $category->id)->count();
                
                // Get example products
                $exampleProducts = Product::where('category_id', $category->id)
                    ->limit(3)
                    ->get(['id', 'name', 'description']);
                    
                $productsByCategory[$category->name] = [
                    'count' => $count,
                    'example_products' => $exampleProducts
                ];
            }
            
            // Simulate what each predicted category would return with detailed breakdown
            $aiCategories = [
                'cheesecake',
                'chocolate',
                'red_velvet',
                'vanilla',
                'strawberry', 
                'black_forest',
                'fruit',
                'extra_cakes'
            ];
            
            $categoryMatchSimulation = [];
            foreach ($aiCategories as $aiCategory) {
                $matches = $this->findMatchingCakes($aiCategory);
                
                // Count occurrences by category
                $matchedCategoryCounts = [];
                foreach ($categoryNames as $catName) {
                    $matchedCategoryCounts[$catName] = 0;
                }
                
                foreach ($matches as $match) {
                    if (isset($match['category'])) {
                        $catName = $match['category'];
                        if (isset($matchedCategoryCounts[$catName])) {
                            $matchedCategoryCounts[$catName]++;
                        }
                    }
                }
                
                // Get top-scoring products for this category
                $topProducts = array_slice($matches, 0, min(3, count($matches)));
                $topProductsInfo = [];
                
                foreach ($topProducts as $prod) {
                    $topProductsInfo[] = [
                        'id' => $prod['id'],
                        'name' => $prod['name'],
                        'category' => $prod['category'],
                        'match_score' => $prod['match_score']
                    ];
                }
                
                $categoryMatchSimulation[$aiCategory] = [
                    'total_matches' => count($matches),
                    'category_breakdown' => $matchedCategoryCounts,
                    'top_matches' => $topProductsInfo
                ];
            }
            
            // Generate diagnostic report for current category mapping
            $diagnosticReport = [];
            $categoryMapping = [
                'cheesecake' => ['Birthday Cakes', 'Cheese Cakes', 'Mix Cakes', 'Special Cakes'],
                'chocolate' => ['Birthday Cakes', 'Mix Cakes', 'Special Cakes'],
                'red_velvet' => ['Birthday Cakes', 'Special Cakes', 'Mix Cakes'],
                'vanilla' => ['Birthday Cakes', 'Mix Cakes'],
                'strawberry' => ['Birthday Cakes', 'Mix Cakes'],
                'black_forest' => ['Birthday Cakes', 'Mix Cakes', 'Special Cakes'],
                'fruit' => ['Mix Cakes', 'Special Cakes']
            ];
            
            foreach ($categoryMapping as $aiCat => $mappedCategories) {
                $diagnosticReport[$aiCat] = [];
                foreach ($mappedCategories as $dbCat) {
                    $count = Product::whereHas('category', function($q) use ($dbCat) {
                        $q->where('name', $dbCat);
                    })->count();
                    
                    $diagnosticReport[$aiCat][$dbCat] = $count;
                }
            }

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'total_products' => $totalProducts,
                    'categories' => $categoryNames,
                    'products_by_category' => $productsByCategory,
                    'ai_category_simulation' => $categoryMatchSimulation,
                    'category_mapping_diagnostic' => $diagnosticReport
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in debugProducts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error debugging products: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get products by category name
     */
    public function getProductsByCategory(Request $request): JsonResponse
    {
        try {
            $categoryName = $request->input('category', '');
            
            if (empty($categoryName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category name is required'
                ], 400);
            }
            
            // Find category by name
            $category = Category::where('name', 'like', '%' . $categoryName . '%')->first();
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found: ' . $categoryName
                ], 404);
            }
            
            // Get products in this category
            $products = Product::where('category_id', $category->id)
                             ->with('category')
                             ->get();
                             
            $formattedProducts = [];
            foreach ($products as $product) {
                $imageUrl = $product->image ? asset('storage/' . $product->image) : null;
                
                $formattedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'price' => (float) $product->price,
                    'price_formatted' => 'Rs. ' . number_format((float)$product->price, 0),
                    'image_url' => $imageUrl,
                    'description' => $product->description ?? 'A delicious ' . $product->name
                ];
            }
            
            return response()->json([
                'success' => true,
                'category' => $category->name,
                'products_count' => count($formattedProducts),
                'products' => $formattedProducts
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getProductsByCategory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting products by category: ' . $e->getMessage()
            ], 500);
        }
    }
} 