<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Services\ImageService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    // Show all products with search, pagination, and sorting
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Get per_page from request, default to 5 if not provided
        $perPage = $request->get('per_page', 5);
        
        // Validate per_page value (only allow specific values)
        $allowedPerPage = [5, 10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        $products = $query->with('category')->oldest()->paginate($perPage);
        
        // Append query parameters to pagination links
        $products->appends($request->query());
        
        return view('admin.products.index', compact('products'));
    }

    // 📌 Show create product form
    public function create()
    {
        $categories = Category::all();
        $allProducts = Product::all();
        return view('admin.products.create', compact('categories', 'allProducts'));
    }

    // 📌 Store product in database
    public function store(Request $request)
{
        try {
            \Log::info('Starting product creation process');
            
            // Debug file upload information
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                \Log::info('Image file details', [
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'size_mb' => round($image->getSize() / (1024 * 1024), 2) . ' MB',
                    'mime' => $image->getMimeType(),
                    'extension' => $image->getClientOriginalExtension(),
                    'error' => $image->getError(),
                    'error_message' => $image->getErrorMessage()
                ]);
            } else {
                \Log::info('No image file received');
            }
            
            // Validate the request
            try {
            $validated = $request->validate([
                    'name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s\(\)\[\]\.\-]+$/',
                    'description' => 'nullable|string|min:10|max:1000',
                'price' => 'required|numeric|min:0|max:999999.99',
                'stock' => 'required|integer|min:0|max:1000',
        'category_id' => 'required|exists:categories,id',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240|dimensions:min_width=100,min_height=100',
                'allergens' => 'nullable|string|max:500',
                'alternative_product_id' => 'nullable|exists:products,id'
            ], [
                    'name.regex' => 'Product name can contain letters, numbers, spaces, brackets, dots, and hyphens',
                    'description.min' => 'Description must be at least 10 characters long if provided',
                'description.max' => 'Description cannot exceed 1000 characters',
                'price.max' => 'Price cannot exceed ₨999,999.99',
                'stock.max' => 'Stock cannot exceed 1000 units',
                'image.dimensions' => 'Image dimensions must be at least 100x100 pixels',
                    'image.max' => 'Image size cannot exceed 10MB (will be compressed to 2MB)'
            ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Validation failed', ['errors' => $e->errors()]);
                
                // Try with more lenient image validation
                $validated = $request->validate([
                    'name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s\(\)\[\]\.\-]+$/',
                    'description' => 'nullable|string|min:10|max:1000',
                    'price' => 'required|numeric|min:0|max:999999.99',
                    'stock' => 'required|integer|min:0|max:1000',
            'category_id' => 'required|exists:categories,id',
                    'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10240'
                ], [
                    'name.regex' => 'Product name can contain letters, numbers, spaces, brackets, dots, and hyphens',
                    'description.min' => 'Description must be at least 10 characters long if provided',
                    'description.max' => 'Description cannot exceed 1000 characters',
                    'price.max' => 'Price cannot exceed ₨999,999.99',
                    'stock.max' => 'Stock cannot exceed 1000 units',
                    'image.max' => 'Image size cannot exceed 10MB (will be compressed to 2MB)'
                ]);
                
                \Log::info('Fallback validation passed');
            }

            \Log::info('Validation passed', ['data' => $validated]);

            // Create new product
            $product = Product::create($validated);

            // Handle image upload
    if ($request->hasFile('image')) {
                \Log::info('Image file received', [
                    'name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(),
                    'mime' => $request->file('image')->getMimeType()
                ]);
                
        $image = $request->file('image');
                
                // Process and compress image
                $path = $this->processProductImage($image);
                
                \Log::info('Image processed successfully', [
                    'path' => $path,
                    'original_size' => $image->getSize(),
                    'new_size' => Storage::disk('public')->size($path),
                    'compression_ratio' => round((1 - Storage::disk('public')->size($path) / $image->getSize()) * 100, 2) . '%'
                ]);
                
                $product->image = $path;
            }

            // Save the product
            if ($product->save()) {
                \Log::info('Product saved successfully', [
                    'id' => $product->id,
                    'image' => $product->image
                ]);
                
                return redirect()->route('products.index')
                    ->with('success', 'Product created successfully.');
            } else {
                throw new \Exception('Failed to save product to database');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Product creation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating product: ' . $e->getMessage());
        }
}

    // 📌 Show edit product form
    public function edit(Product $product)
    {
        $categories = Category::all();
        $allProducts = Product::all();
        return view('admin.products.edit', compact('product', 'categories', 'allProducts'));
    }

    // 📌 Update product in database
    public function update(Request $request, Product $product)
{
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s\(\)\[\]\.\-]+$/',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'is_active' => 'boolean',
            'allergens' => 'nullable|string|max:500',
            'alternative_product_id' => 'nullable|exists:products,id'
        ], [
            'name.required' => 'Product name is required',
            'name.max' => 'Product name cannot exceed 255 characters',
            'name.regex' => 'Product name can contain letters, numbers, spaces, brackets, dots, and hyphens',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category is invalid',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price cannot be negative',
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be a whole number',
            'stock.min' => 'Stock cannot be negative',
            'description.required' => 'Product description is optional',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be in jpeg, png, jpg, or gif format',
            'image.max' => 'Image size cannot exceed 10MB (will be compressed to 2MB)'
    ]);

    if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            // Process and compress new image
            $image = $request->file('image');
            $path = $this->processProductImage($image);
            $validated['image'] = $path;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
}

    // 📌 Delete product and remove image
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    // 📌 Show products of a specific category
    public function showByCategory($categoryId)
    {
        $category = Category::with('products')->findOrFail($categoryId);
        $products = $category->products()->paginate(5); // ✅ Paginate products under category
        return view('admin.products.index', compact('category', 'products'));
    }

    // 📌 Handle Image Upload & Compression
    private function uploadAndCompressImage($image)
    {
        $filename = time() . '.' . $image->getClientOriginalExtension();
        $resizedImage = Image::make($image)->resize(500, 500)->encode('jpg', 75); // ✅ Resize & Compress
        Storage::put("public/products/{$filename}", $resizedImage);
        return "products/{$filename}";
    }

    /**
     * Process and compress product image to ensure size is under 2MB
     */
    private function processProductImage($image)
    {
        $filename = time() . '_' . $image->getClientOriginalName();
        
        // Create image manager with GD driver
        $manager = new ImageManager(new Driver());
        
        // Read the image
        $img = $manager->read($image->getRealPath());
        
        // Get original dimensions
        $width = $img->width();
        $height = $img->height();
        
        // Calculate new dimensions while maintaining aspect ratio
        $maxDimension = 800;
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = (int) ($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int) ($width * ($maxDimension / $height));
        }
        
        // Resize image
        $img->resize($newWidth, $newHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Adaptive compression to ensure size is under 2MB
        $quality = 80; // Start with 80% quality
        $maxSize = 2 * 1024 * 1024; // 2MB in bytes
        
        do {
            // Save with current quality
            $path = 'products/' . $filename;
            Storage::disk('public')->put($path, $img->toJpeg($quality));
            
            $currentSize = Storage::disk('public')->size($path);
            
            // If still too large, reduce quality
            if ($currentSize > $maxSize && $quality > 20) {
                Storage::disk('public')->delete($path);
                $quality -= 10; // Reduce quality by 10%
            } else {
                break; // Size is acceptable
            }
        } while ($quality > 20);
        
        // Log compression details
        \Log::info('Image compression completed', [
            'original_size' => $image->getSize(),
            'final_size' => Storage::disk('public')->size($path),
            'final_quality' => $quality,
            'compression_ratio' => round((1 - Storage::disk('public')->size($path) / $image->getSize()) * 100, 2) . '%',
            'dimensions' => [$newWidth, $newHeight]
        ]);
        
        return $path;
    }

    public function deleteImage(Product $product)
{
    if ($product->image) {
            Storage::disk('public')->delete($product->image);
            $product->image = null;
            $product->save();
        }

        return redirect()->route('products.edit', $product)
            ->with('success', 'Product image deleted successfully.');
}

    // 📌 Export CSV of cake product images only
    public function exportCakeImagesCsv()
    {
        $products = Product::with('category')
            ->whereHas('category', function ($q) {
                $q->where('name', 'like', '%cake%');
            })
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cake_images.csv"',
        ];

        $callback = function () use ($products) {
            $output = fopen('php://output', 'w');
            // CSV Header
            fputcsv($output, ['id', 'name', 'category', 'image_path', 'image_url']);

            foreach ($products as $product) {
                $imagePath = $product->image ?? '';
                $imageUrl = $imagePath ? asset('storage/' . $imagePath) : '';
                fputcsv($output, [
                    $product->id,
                    $product->name,
                    optional($product->category)->name,
                    $imagePath,
                    $imageUrl,
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
}
}
