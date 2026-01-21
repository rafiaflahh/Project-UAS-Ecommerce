<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'stock'])->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|max:255|unique:products',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $imageUrl = '/storage/products/' . $imageName;
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active ?? true,
            'image_url' => $imageUrl,
        ]);

        $product->stock()->create([
            'quantity' => $request->quantity,
            'reserved_quantity' => 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'stock', 'transactionItems']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active ?? true,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_url && str_contains($product->image_url, '/storage/products/')) {
                $oldImagePath = str_replace('/storage/', '', $product->image_url);
                if (file_exists(storage_path('app/public/' . $oldImagePath))) {
                    unlink(storage_path('app/public/' . $oldImagePath));
                }
            }

            // Upload new image
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $updateData['image_url'] = '/storage/products/' . $imageName;
        }

        $product->update($updateData);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->transactionItems()->count() > 0) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Cannot delete product with associated transactions.');
        }

        $product->stock()->delete();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
