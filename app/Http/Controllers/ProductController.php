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
        $search = $request->input('search');
        $products = Product::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('id', 'asc')
            ->paginate(5); // ✅ Using Laravel's pagination

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
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'description' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            // ✅ Create ImageManager instance with GD driver
            $manager = new ImageManager(new Driver());
            $resizedImage = $manager->read($image->getRealPath())
                                    ->resize(500, 500)
                                    ->toJpeg(75);

            // ✅ Save Compressed Image to Storage
            Storage::put("public/products/{$filename}", $resizedImage);
            $imagePath = "products/{$filename}";
        }

        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'image' => $imagePath,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with('success', 'Product added successfully!');
    }

    // 📌 Show edit product form
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 📌 Update product in database
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            // ✅ Delete Old Image (If Exists)
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            // ✅ Create ImageManager instance with GD driver
            $manager = new ImageManager(new Driver());
            $resizedImage = $manager->read($image->getRealPath())
                                    ->resize(500, 500)
                                    ->toJpeg(75);

            // ✅ Save Compressed Image to Storage
            Storage::put("public/products/{$filename}", $resizedImage);
            $product->image = "products/{$filename}";
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    // 📌 Delete product and remove image
    public function destroy(Product $product)
    {
        // ✅ Delete product image from storage
        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
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
            if (Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }
            $product->update(['image' => null]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
