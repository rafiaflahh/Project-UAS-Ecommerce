<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Cache categories for better performance
        $categories = Cache::remember('active_categories', 3600, function () {
            return Category::where('is_active', true)
                ->select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->get();
        });

        // Build optimized product query
        $products = Product::active()
            ->withRelations()
            ->when($request->category, function ($query, $categoryId) {
                $query->byCategory($categoryId);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->latest()
            ->paginate(12);

        return view('shop.index', compact('categories', 'products'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load(['category', 'stock']);
        
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['category', 'stock'])
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }

    public function category(Category $category)
    {
        if (!$category->is_active) {
            abort(404);
        }

        $products = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->with(['category', 'stock'])
            ->latest()
            ->paginate(12);

        return view('shop.category', compact('category', 'products'));
    }
}
