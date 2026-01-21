<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCheckoutRequest;
use App\Services\CartService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $transactionService;

    public function __construct(CartService $cartService, TransactionService $transactionService)
    {
        $this->cartService = $cartService;
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $stockErrors = $this->cartService->validateStock();
        
        if (!empty($stockErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items in your cart are no longer available or have insufficient stock.')
                ->with('stock_errors', $stockErrors);
        }

        $cart = $this->cartService->getWithProducts();
        $total = $this->cartService->getTotal();

        return view('checkout.index', compact('cart', 'total'));
    }

public function process(ProcessCheckoutRequest $request)
    {
        // Authorization is handled in the ProcessCheckoutRequest

        $cart = $this->cartService->getWithProducts();
        $cartItems = $cart->map(function ($item) {
            return [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ];
        })->toArray();

        $transactionData = [
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ];

        try {
            // Process transaction using service with automatic rollback on failure
            $result = $this->transactionService->processTransaction($cartItems, $transactionData);

            if ($result['success']) {
                // Clear cart after successful transaction
                $this->cartService->clear();

                return redirect()->route('checkout.success', $result['transaction'])
                    ->with('success', $result['message']);
            }

            return redirect()->route('checkout.index')
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            // Exception is caught here - transaction has already been rolled back
            return redirect()->route('checkout.index')
                ->with('error', 'An error occurred while processing your order: ' . $e->getMessage());
        }
    }

    public function success($transactionId)
    {
        $transaction = \App\Models\Transaction::with(['transactionItems.product.category', 'user'])
            ->findOrFail($transactionId);

        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        return view('checkout.success', compact('transaction'));
    }
}
