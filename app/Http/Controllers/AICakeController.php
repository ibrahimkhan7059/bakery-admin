<?php

namespace App\Http\Controllers;

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
        try {
            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max
            ]);

            // Get the uploaded image
            $image = $request->file('image');
            
            // Save image temporarily
            $tempPath = $image->store('temp/ai-uploads', 'public');
            $fullPath = Storage::disk('public')->path($tempPath);

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

            // Find matching cakes from Laravel database
            $matchingCakes = $this->findMatchingCakes($predictedCategory);

            return response()->json([
                'success' => true,
                'prediction' => $prediction,
                'matching_cakes' => $matchingCakes,
                'message' => "Found " . count($matchingCakes) . " matching cakes in " . ucfirst(str_replace('_', ' ', $predictedCategory)) . " category"
            ]);

        } catch (\Exception $e) {
            Log::error('AI Cake Prediction Error: ' . $e->getMessage());
            
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
            // Prepare the request to Python API
            $response = Http::timeout(60)->attach(
                'image',
                file_get_contents($imagePath),
                basename($imagePath)
            )->post(config('app.ai_api_url', 'http://192.168.100.81:5000') . '/predict_cake');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'AI API responded with error: ' . $response->status(),
                'error' => 'api_error'
            ];

        } catch (\Exception $e) {
            Log::error('AI API Call Error: ' . $e->getMessage());
            
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
            // Map AI categories to database categories
            $categoryMapping = [
                'cheesecake' => ['cheesecake', 'cheese cake', 'cheese-cake'],
                'chocolate' => ['chocolate', 'chocolate cake', 'chocolate-cake'],
                'red_velvet' => ['red velvet', 'red-velvet', 'red velvet cake'],
                'extra_cakes' => ['cake', 'pastry', 'dessert', 'sweet']
            ];

            $searchTerms = $categoryMapping[$predictedCategory] ?? ['cake'];
            
            // Get all cake categories
            $cakeCategories = Category::where('is_active', true)
                                ->where(function ($query) {
                                    $query->where('name', 'like', '%cake%')
                                          ->orWhere('name', 'like', '%pastry%')
                                          ->orWhere('name', 'like', '%dessert%');
                                })
                                ->pluck('id')
                                ->toArray();
            
            // If we don't have any categories, fall back to search terms
            if (empty($cakeCategories)) {
                // Search in products table using original logic
                $query = Product::query();

                foreach ($searchTerms as $term) {
                    $query->orWhere('name', 'like', '%' . $term . '%')
                          ->orWhere('description', 'like', '%' . $term . '%');
                }

                // Also search by category name
                $query->orWhereHas('category', function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('name', 'like', '%' . $term . '%');
                    }
                });

                $matchingProducts = $query->with('category')
                                        ->where('is_active', true)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(8)
                                        ->get();
            } else {
                // Get products from each cake category
                $matchingProducts = collect();
                
                // Try to get at least one product from each category
                foreach ($cakeCategories as $categoryId) {
                    $productsInCategory = Product::where('category_id', $categoryId)
                                                ->where('is_active', true)
                                                ->with('category')
                                                ->orderBy('created_at', 'desc')
                                                ->take(2)
                                                ->get();
                    
                    $matchingProducts = $matchingProducts->merge($productsInCategory);
                }
                
                // If we don't have enough products (at least 8), add more using search terms
                if ($matchingProducts->count() < 8) {
                    // Search in products table using original logic
                    $query = Product::query();

                    foreach ($searchTerms as $term) {
                        $query->orWhere('name', 'like', '%' . $term . '%')
                              ->orWhere('description', 'like', '%' . $term . '%');
                    }

                    $additionalProducts = $query->with('category')
                                            ->where('is_active', true)
                                            ->whereNotIn('id', $matchingProducts->pluck('id')->toArray())
                                            ->orderBy('created_at', 'desc')
                                            ->limit(8 - $matchingProducts->count())
                                            ->get();
                    
                    $matchingProducts = $matchingProducts->merge($additionalProducts);
                }
                
                // Take only 8 products max
                $matchingProducts = $matchingProducts->take(8);
            }

            // Format response for Flutter app
            $formattedCakes = [];
            
            // Create an array to track how many products we have from each category
            $categoryCount = [];
            
            // Get distinct categories we have in our results for better distribution
            $distinctCategories = $matchingProducts->pluck('category.name')->filter()->unique()->values()->toArray();
            
            // Make sure each category gets at least one product in the results
            foreach ($matchingProducts as $product) {
                $categoryName = $product->category->name ?? 'Cake';
                
                // Initialize counter if not exists
                if (!isset($categoryCount[$categoryName])) {
                    $categoryCount[$categoryName] = 0;
                }
                
                // Only add this product if we haven't reached our category limit (at least 1 from each)
                if (count($formattedCakes) < 8 && 
                    ($categoryCount[$categoryName] < 1 || count($distinctCategories) <= 4)) {
                    
                    $formattedCakes[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category' => $categoryName,
                        'price' => (float) $product->price,
                        'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                        'description' => $product->description ?? 'Delicious cake description',
                        'match_score' => $this->calculateMatchScore($product, $predictedCategory),
                        'laravel_product_id' => $product->id, // For cart integration
                        'available_quantity' => $product->quantity ?? 1,
                        'is_available' => $product->is_active && ($product->quantity ?? 1) > 0,
                        'category_color' => $this->getCategoryColor($categoryName) // Add color for category
                    ];
                    
                    // Increment the counter for this category
                    $categoryCount[$categoryName]++;
                }
            }
            
            // If we don't have 4 products yet, add more from any category
            if (count($formattedCakes) < 4) {
                foreach ($matchingProducts as $product) {
                    $categoryName = $product->category->name ?? 'Cake';
                    
                    // Check if this product is already added
                    $alreadyAdded = false;
                    foreach ($formattedCakes as $cake) {
                        if ($cake['id'] == $product->id) {
                            $alreadyAdded = true;
                            break;
                        }
                    }
                    
                    // Add this product if not already added and we haven't reached our limit
                    if (!$alreadyAdded && count($formattedCakes) < 8) {
                        $formattedCakes[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'category' => $categoryName,
                            'price' => (float) $product->price,
                            'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                            'description' => $product->description ?? 'Delicious cake description',
                            'match_score' => $this->calculateMatchScore($product, $predictedCategory),
                            'laravel_product_id' => $product->id, // For cart integration
                            'available_quantity' => $product->quantity ?? 1,
                            'is_available' => $product->is_active && ($product->quantity ?? 1) > 0,
                            'category_color' => $this->getCategoryColor($categoryName) // Add color for category
                        ];
                    }
                }
            }

            // Sort by match score (highest first)
            usort($formattedCakes, function ($a, $b) {
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
        $baseScore = 85.0; // Base score for all matches
        
        // Boost score for exact category matches
        if (str_contains(strtolower($product->category->name ?? ''), str_replace('_', ' ', $predictedCategory))) {
            $baseScore += 10.0;
        }
        
        // Boost score for name matches
        if (str_contains(strtolower($product->name), str_replace('_', ' ', $predictedCategory))) {
            $baseScore += 5.0;
        }
        
        // Random variation to make it realistic
        $variation = rand(-5, 5);
        
        return min(100.0, max(70.0, $baseScore + $variation));
    }

    /**
     * Get AI service health status
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->get(
                config('app.ai_api_url', 'http://192.168.100.81:5000') . '/health'
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
     * Get a color code for a specific cake category
     */
    private function getCategoryColor(string $categoryName): string
    {
        // Convert category name to lowercase for comparison
        $lowerCategoryName = strtolower($categoryName);
        
        // Map categories to color codes
        if (str_contains($lowerCategoryName, 'birthday')) {
            return '#FF5722'; // Deep Orange
        } elseif (str_contains($lowerCategoryName, 'cheese')) {
            return '#FFEB3B'; // Yellow
        } elseif (str_contains($lowerCategoryName, 'mix')) {
            return '#4CAF50'; // Green
        } elseif (str_contains($lowerCategoryName, 'special')) {
            return '#2196F3'; // Blue
        } else {
            return '#9C27B0'; // Default Purple
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
} 