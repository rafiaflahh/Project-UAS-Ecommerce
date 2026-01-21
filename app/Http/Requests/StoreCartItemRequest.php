<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id,is_active,1'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                function ($attribute, $value, $fail) {
                    $product = \App\Models\Product::find($this->product_id);
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
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product is not available.',
            'quantity.required' => 'Please specify a quantity.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Maximum quantity per order is 100.',
        ];
    }
}
