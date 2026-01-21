<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $product = \App\Models\Product::find($this->route('product'));
        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->get();

        return $cart->has($product->id);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                function ($attribute, $value, $fail) {
                    $product = \App\Models\Product::find($this->route('product'));
                    if (!$product) return;
                    
                    $availableStock = $product->available_stock;
                    if ($value > $availableStock) {
                        $fail("Only {$availableStock} items are available for this product.");
                    }
                }
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Please specify a quantity.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Maximum quantity per order is 100.',
        ];
    }
}
