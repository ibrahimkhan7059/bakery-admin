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
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        return response()->json($products);
    }

    public function show(Product $product)
    {
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'category_id' => $product->category_id,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'allergens' => $product->allergens 
                ? array_map('trim', explode(',', $product->allergens)) 
                : [],
            'alternative_product' => $product->alternative_product_id
                ? Product::find($product->alternative_product_id)
                : null,
        ];

        // If alternative_product exists, return its basic info
        if ($productData['alternative_product']) {
            $alt = $productData['alternative_product'];
            $productData['alternative_product'] = [
                'id' => $alt->id,
                'name' => $alt->name,
                'description' => $alt->description,
                'price' => $alt->price,
                'image' => $alt->image ? asset('storage/' . $alt->image) : null,
                'category_id' => $alt->category_id,
                'category' => $alt->category ? [
                    'id' => $alt->category->id,
                    'name' => $alt->category->name,
                ] : null,
                'created_at' => $alt->created_at,
                'updated_at' => $alt->updated_at,
            ];
        }

        return response()->json($productData);
    }

    public function byCategory(Category $category)
    {
        $products = Product::with('category')
            ->where('category_id', $category->id)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        return response()->json($products);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $category = $request->input('category');
        $minPrice = $request->input('min_price', 0);
        $maxPrice = $request->input('max_price', 1000000);
        $sortBy = $request->input('sort_by', 'name');

        $products = Product::with('category')
            ->when($query, function ($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
            })
            ->when($category, function ($q) use ($category) {
                return $q->whereHas('category', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            })
            ->whereBetween('price', [$minPrice, $maxPrice])
            ->when($sortBy, function ($q) use ($sortBy) {
                switch ($sortBy) {
                    case 'price_asc':
                        return $q->orderBy('price', 'asc');
                    case 'price_desc':
                        return $q->orderBy('price', 'desc');
                    case 'newest':
                        return $q->orderBy('created_at', 'desc');
                    default:
                        return $q->orderBy('name', 'asc');
                }
            })
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        return response()->json($products);
    }
}