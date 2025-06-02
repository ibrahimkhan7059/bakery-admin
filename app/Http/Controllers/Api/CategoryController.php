<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'products_count' => $category->products_count,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            });

        return response()->json($categories);
    }

    public function show(Category $category)
    {
        $categoryData = [
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->image ? asset('storage/' . $category->image) : null,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];

        return response()->json($categoryData);
    }
} 