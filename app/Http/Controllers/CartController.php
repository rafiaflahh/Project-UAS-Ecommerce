<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getWithProducts();
        $total = $this->cartService->getTotal();
        
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(StoreCartItemRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        if ($this->cartService->add($product, $request->quantity)) {
            return redirect()->route('cart.index')
                ->with('success', 'Product added to cart successfully!');
        }

        return redirect()->back()
            ->with('error', 'Insufficient stock for this product.');
    }

    public function update(UpdateCartItemRequest $request, Product $product)
    {
        if ($request->quantity == 0) {
            $this->cartService->remove($product);
            return redirect()->route('cart.index')
                ->with('success', 'Product removed from cart.');
        }

        if ($this->cartService->update($product, $request->quantity)) {
            return redirect()->route('cart.index')
                ->with('success', 'Cart updated successfully!');
        }

        return redirect()->route('cart.index')
            ->with('error', 'Insufficient stock for this quantity.');
    }

    public function remove(Product $product)
    {
        // Authorize that user can remove this item from cart
        Gate::authorize('manage-cart');

        $this->cartService->remove($product);
        
        return redirect()->route('cart.index')
            ->with('success', 'Product removed from cart.');
    }

    public function clear()
    {
        $this->cartService->clear();
        
        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared successfully.');
    }
}
