<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->get();

        return response()->json($products);
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product->load('category'));
    }

    public function byCategory(Category $category)
    {
        $products = Product::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->get();

        return response()->json($products);
    }
} 