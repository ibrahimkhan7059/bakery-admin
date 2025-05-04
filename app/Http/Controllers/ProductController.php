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
    // 📌 Show all products with search, pagination, and sorting
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->with('category')->oldest()->paginate(5);
        return view('admin.products.index', compact('products'));
    }

    // 📌 Show create product form
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // 📌 Store product in database
    public function store(Request $request)
    {
        try {
            \Log::info('Starting product creation process');
            
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255|regex:/^[A-Za-z\s\(\)\[\]]+$/',
                'description' => 'required|string|min:10|max:1000',
                'price' => 'required|numeric|min:0|max:999999.99',
                'stock' => 'required|integer|min:0|max:1000',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100'
            ], [
                'name.regex' => 'Product name can only contain letters, spaces, and brackets',
                'description.min' => 'Description must be at least 10 characters long',
                'description.max' => 'Description cannot exceed 1000 characters',
                'price.max' => 'Price cannot exceed ₨999,999.99',
                'stock.max' => 'Stock cannot exceed 1000 units',
                'image.dimensions' => 'Image dimensions must be at least 100x100 pixels',
                'image.max' => 'Image size cannot exceed 2MB'
            ]);

            \Log::info('Validation passed', ['data' => $validated]);

            // Create new product
            $product = new Product();
            $product->name = $validated['name'];
            $product->description = $validated['description'];
            $product->price = $validated['price'];
            $product->stock = $validated['stock'];
            $product->category_id = $validated['category_id'];

            // Handle image upload
            if ($request->hasFile('image')) {
                \Log::info('Image file received', [
                    'name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(),
                    'mime' => $request->file('image')->getMimeType()
                ]);
                
                $image = $request->file('image');
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
                
                // Save compressed image with higher compression
                $path = 'products/' . $filename;
                $fullPath = storage_path('app/public/' . $path);
                
                \Log::info('Attempting to save image', [
                    'path' => $path,
                    'full_path' => $fullPath,
                    'original_size' => $image->getSize(),
                    'new_dimensions' => [$newWidth, $newHeight]
                ]);
                
                // Ensure directory exists
                if (!file_exists(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0755, true);
                }
                
                // Save with higher compression (60% quality)
                Storage::disk('public')->put($path, $img->toJpeg(60));
                
                \Log::info('Image saved successfully', [
                    'path' => $path,
                    'exists' => Storage::disk('public')->exists($path),
                    'new_size' => Storage::disk('public')->size($path)
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
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 📌 Update product in database
    public function update(Request $request, Product $product)
    {
        try {
            \Log::info('Starting product update process', ['product_id' => $product->id]);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|regex:/^[A-Za-z\s\(\)\[\]]+$/',
                'description' => 'required|string|min:10|max:1000',
                'price' => 'required|numeric|min:0|max:999999.99',
                'stock' => 'required|integer|min:0|max:1000',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100'
            ], [
                'name.regex' => 'Product name can only contain letters, spaces, and brackets',
                'description.min' => 'Description must be at least 10 characters long',
                'description.max' => 'Description cannot exceed 1000 characters',
                'price.max' => 'Price cannot exceed ₨999,999.99',
                'stock.max' => 'Stock cannot exceed 1000 units',
                'image.dimensions' => 'Image dimensions must be at least 100x100 pixels',
                'image.max' => 'Image size cannot exceed 2MB'
            ]);

            $product->name = $validated['name'];
            $product->description = $validated['description'];
            $product->price = $validated['price'];
            $product->stock = $validated['stock'];
            $product->category_id = $validated['category_id'];

            if ($request->hasFile('image')) {
                \Log::info('New image file received', [
                    'name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(),
                    'mime' => $request->file('image')->getMimeType()
                ]);
                
                // Delete old image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }

                $image = $request->file('image');
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
                
                // Save compressed image with higher compression
                $path = 'products/' . $filename;
                $fullPath = storage_path('app/public/' . $path);
                
                \Log::info('Attempting to save new image', [
                    'path' => $path,
                    'full_path' => $fullPath,
                    'original_size' => $image->getSize(),
                    'new_dimensions' => [$newWidth, $newHeight]
                ]);
                
                // Ensure directory exists
                if (!file_exists(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0755, true);
                }
                
                // Save with higher compression (60% quality)
                Storage::disk('public')->put($path, $img->toJpeg(60));
                
                \Log::info('New image saved successfully', [
                    'path' => $path,
                    'exists' => Storage::disk('public')->exists($path),
                    'new_size' => Storage::disk('public')->size($path)
                ]);
                
                $product->image = $path;
            }

            if ($product->save()) {
                \Log::info('Product updated successfully', [
                    'id' => $product->id,
                    'image' => $product->image
                ]);
                
                return redirect()->route('products.index')
                    ->with('success', 'Product updated successfully.');
            } else {
                throw new \Exception('Failed to update product in database');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Product update error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating product: ' . $e->getMessage());
        }
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
}
