<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;


class CategoryController extends Controller
{
    // 📌 Show all categories
    public function index(Request $request)
{
    // Get the search query from the request
    $search = $request->input('search');

    // Query Builder with Search and Pagination
    $categories = Category::where('name', 'like', '%' . $search . '%')
                          ->orderBy('id', 'asc')
                          ->paginate(5); // ✅ Pagination added

    // Return the view with the filtered or paginated categories
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
        $request->validate(['name' => 'required|unique:categories']);
        Category::create(['name' => $request->name]);
        return redirect()->route('categories.index')->with('success', 'Category added successfully!');
    }

    // 📌 Show edit form
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // 📌 Update category
    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|unique:categories,name,' . $category->id]);
        $category->update(['name' => $request->name]);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    // 📌 Delete category
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
