<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return response()->json($categories);
    }

    public function show(Category $category)
    {
        if (!$category->is_active) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category->load(['products' => function($query) {
            $query->where('is_active', true);
        }]));
    }
} 