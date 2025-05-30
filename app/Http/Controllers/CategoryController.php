<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Routing\Controller;


class CategoryController extends Controller
{
    // 📌 Show all categories
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->withCount('products')->oldest()->paginate(5);
        return view('admin.categories.index', compact('categories'));
    }


    // 📌 Show create form
    public function create()
    {
        return view('admin.categories.create');
    }

    // 📌 Store new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:categories,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100'
        ], [
            'name.regex' => 'Category name can only contain letters and spaces.',
            'name.unique' => 'This category name already exists.',
            'image.dimensions' => 'The image must be at least 100x100 pixels.',
            'image.max' => 'The image size must not exceed 2MB.'
        ]);

        $category = new Category();
        $category->name = $request->name;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Create image manager with GD driver
            $manager = new ImageManager(new Driver());
            
            // Compress and resize image
            $img = $manager->read($image->getRealPath());
            $img->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save compressed image
            Storage::disk('public')->put('categories/' . $filename, $img->toJpeg(75));
            $category->image = 'categories/' . $filename;
        }

        $category->save();

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    // 📌 Show edit form
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // 📌 Update category
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:categories,name,' . $category->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100'
        ], [
            'name.regex' => 'Category name can only contain letters and spaces.',
            'name.unique' => 'This category name already exists.',
            'image.dimensions' => 'The image must be at least 100x100 pixels.',
            'image.max' => 'The image size must not exceed 2MB.'
        ]);

        $category->name = $request->name;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Create image manager with GD driver
            $manager = new ImageManager(new Driver());
            
            // Compress and resize image
            $img = $manager->read($image->getRealPath());
            $img->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save compressed image
            Storage::disk('public')->put('categories/' . $filename, $img->toJpeg(75));
            $category->image = 'categories/' . $filename;
        }

        $category->save();

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    // 📌 Delete category
    public function destroy(Category $category)
    {
        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }

    public function products(Category $category)
    {
        $products = $category->products()->paginate(10);
        return view('admin.categories.products', compact('category', 'products'));
    }
}
