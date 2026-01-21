<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function get(): Collection
    {
        return collect(Session::get('cart', []));
    }

    public function add(Product $product, int $quantity = 1): bool
    {
        if (!$product->isInStock() || $product->available_stock < $quantity) {
            return false;
        }

        $cart = $this->get();
        $itemKey = $product->id;

        if ($cart->has($itemKey)) {
            $currentQuantity = $cart->get($itemKey)['quantity'];
            $newQuantity = $currentQuantity + $quantity;
            
            if ($product->available_stock < $newQuantity) {
                return false;
            }
            
            $cart->put($itemKey, [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $newQuantity,
                'subtotal' => $product->price * $newQuantity,
            ]);
        } else {
            $cart->put($itemKey, [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $product->price * $quantity,
            ]);
        }

        Session::put('cart', $cart->toArray());
        return true;
    }

    public function update(Product $product, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->remove($product);
        }

        if ($product->available_stock < $quantity) {
            return false;
        }

        $cart = $this->get();
        $itemKey = $product->id;

        if ($cart->has($itemKey)) {
            $cart->put($itemKey, [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $product->price * $quantity,
            ]);
            Session::put('cart', $cart->toArray());
            return true;
        }

        return false;
    }

    public function remove(Product $product): bool
    {
        $cart = $this->get();
        $itemKey = $product->id;

        if ($cart->has($itemKey)) {
            $cart->forget($itemKey);
            Session::put('cart', $cart->toArray());
            return true;
        }

        return false;
    }

    public function clear(): void
    {
        Session::forget('cart');
    }

    public function getTotal(): float
    {
        return $this->get()->sum('subtotal');
    }

    public function getTotalItems(): int
    {
        return $this->get()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->get()->isEmpty();
    }

    public function validateStock(): array
    {
        $errors = [];
        $cart = $this->get();

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || !$product->isInStock() || $product->available_stock < $item['quantity']) {
                $errors[$item['product_id']] = [
                    'product' => $product,
                    'requested' => $item['quantity'],
                    'available' => $product ? $product->available_stock : 0,
                ];
            }
        }

        return $errors;
    }

    public function getWithProducts(): Collection
    {
        $cart = $this->get();
        $productIds = $cart->keys();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $products = Product::whereIn('id', $productIds)
            ->with(['category', 'stock'])
            ->get()
            ->keyBy('id');

        return $cart->map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            return array_merge($item, [
                'product' => $product,
                'available_stock' => $product ? $product->available_stock : 0,
                'in_stock' => $product && $product->isInStock(),
            ]);
        });
    }
}