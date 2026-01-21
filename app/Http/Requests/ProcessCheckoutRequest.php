<?php

namespace App\Http\Requests;

use App\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        parent::__construct();
        $this->cartService = $cartService;
    }

    /**
     * Determine if user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        // Check if cart is not empty
        if ($this->cartService->isEmpty()) {
            return false;
        }

        // Validate stock availability for all cart items
        $stockErrors = $this->cartService->validateStock();
        return empty($stockErrors);
    }

    /**
     * Get validation rules that apply to request.
     */
    public function rules(): array
    {
        return [
            'address' => [
                'required',
                'string',
                'min:10',
                'max:500'
            ],
            'payment_method' => [
                'required',
                'string',
                'in:CASH_ON_DELIVERY'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[\w\s\.,!?@#$%^&*()\-+]*$/'
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'address.required' => 'Delivery address is required.',
            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address must not exceed 500 characters.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method selected.',
            'notes.max' => 'Order notes must not exceed 1000 characters.',
            'notes.regex' => 'Order notes contain invalid characters.',
        ];
    }

    /**
     * Get custom validation attributes.
     */
    public function attributes(): array
    {
        return [
            'address' => 'delivery address',
            'payment_method' => 'payment method',
            'notes' => 'order notes',
        ];
    }
}