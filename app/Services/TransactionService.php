<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    /**
     * Process a complete transaction with proper stock management and database consistency
     */
    public function processTransaction(array $cartItems, array $transactionData = []): array
    {
        return DB::transaction(function () use ($cartItems, $transactionData) {
            try {
                // Step 1: Validate all stock availability before proceeding
                $this->validateCartStock($cartItems);

                // Step 2: Calculate total amount
                $totalAmount = $this->calculateTotal($cartItems);

                // Step 3: Create transaction header
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'transaction_number' => $this->generateTransactionNumber(),
                    'total_amount' => $totalAmount,
                    'status' => 'PROCESSING',
                    'notes' => $transactionData['notes'] ?? null,
                ]);

                // Step 4: Process each item with stock reservation
                $this->processTransactionItems($transaction, $cartItems);

                // Step 5: Confirm stock deduction and complete transaction
                $this->confirmStockDeduction($transaction);

                // Step 6: Update transaction status to completed
                $transaction->update(['status' => 'COMPLETED']);

                return [
                    'success' => true,
                    'transaction' => $transaction->fresh(['transactionItems.product.category']),
                    'message' => 'Transaction completed successfully'
                ];

            } catch (\Exception $e) {
                // This will automatically trigger rollback in DB::transaction
                throw $e;
            }
        });
    }

    /**
     * Validate that all cart items have sufficient stock
     */
    private function validateCartStock(array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            if (!$product->is_active) {
                throw new \Exception("Product '{$product->name}' is no longer available");
            }

            if (!$product->stock) {
                throw new \Exception("Stock information not found for '{$product->name}'");
            }

            if ($product->stock->available_quantity < $item['quantity']) {
                throw new \Exception(
                    "Insufficient stock for '{$product->name}'. Available: {$product->stock->available_quantity}, Requested: {$item['quantity']}"
                );
            }
        }
    }

    /**
     * Calculate total amount for the transaction
     */
    private function calculateTotal(array $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    /**
     * Generate unique transaction number
     */
    private function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = date('Ymd');
        $sequence = Transaction::whereDate('created_at', today())->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Process transaction items and reserve stock
     */
    private function processTransactionItems(Transaction $transaction, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = Product::findOrFail($item['product_id']);
            $stock = $product->stock;

            // Reserve stock first (prevents race conditions)
            if (!$stock->reserveStock($item['quantity'])) {
                throw new \Exception("Failed to reserve stock for '{$product->name}'");
            }

            // Create transaction item
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }
    }

    /**
     * Confirm stock deduction for all transaction items
     */
    private function confirmStockDeduction(Transaction $transaction): void
    {
        foreach ($transaction->transactionItems as $item) {
            $product = $item->product;
            $stock = $product->stock;

            // Confirm the stock deduction
            if (!$stock->confirmStock($item->quantity)) {
                throw new \Exception("Failed to confirm stock deduction for '{$product->name}'");
            }
        }
    }

    /**
     * Cancel a transaction and restore stock
     */
    public function cancelTransaction(Transaction $transaction): array
    {
        return DB::transaction(function () use ($transaction) {
            try {
                if ($transaction->isCompleted()) {
                    // Restore stock for completed transactions
                    $this->restoreStock($transaction);
                }

                // Update transaction status
                $transaction->update(['status' => 'CANCELLED']);

                return [
                    'success' => true,
                    'message' => 'Transaction cancelled successfully'
                ];

            } catch (\Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * Restore stock when transaction is cancelled
     */
    private function restoreStock(Transaction $transaction): void
    {
        foreach ($transaction->transactionItems as $item) {
            $product = $item->product;
            $stock = $product->stock;

            // Release reserved stock and add back to available quantity
            if (!$stock->releaseStock($item->quantity)) {
                throw new \Exception("Failed to release stock for '{$product->name}'");
            }

            // Add the quantity back to available stock
            $stock->quantity += $item->quantity;
            $stock->save();
        }
    }

    /**
     * Update transaction status with proper validation
     */
    public function updateTransactionStatus(Transaction $transaction, string $newStatus): array
    {
        $validTransitions = [
            'PENDING' => ['PROCESSING', 'CANCELLED'],
            'PROCESSING' => ['COMPLETED', 'CANCELLED'],
            'COMPLETED' => [],
            'CANCELLED' => [],
        ];

        if (!isset($validTransitions[$transaction->status])) {
            throw new \Exception("Invalid current transaction status");
        }

        if (!in_array($newStatus, $validTransitions[$transaction->status])) {
            throw new \Exception("Cannot transition from {$transaction->status} to {$newStatus}");
        }

        $transaction->update(['status' => $newStatus]);

        return [
            'success' => true,
            'message' => "Transaction status updated to {$newStatus}"
        ];
    }

    /**
     * Get transaction statistics for a user
     */
    public function getUserTransactionStats(int $userId): array
    {
        $transactions = Transaction::where('user_id', $userId);

        return [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('total_amount'),
            'completed_transactions' => $transactions->where('status', 'COMPLETED')->count(),
            'pending_transactions' => $transactions->where('status', 'PENDING')->count(),
            'processing_transactions' => $transactions->where('status', 'PROCESSING')->count(),
            'cancelled_transactions' => $transactions->where('status', 'CANCELLED')->count(),
            'average_order_value' => $transactions->where('status', 'COMPLETED')->avg('total_amount') ?? 0,
        ];
    }

    /**
     * Get detailed transaction report for admin
     */
    public function getTransactionReport(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Transaction::with(['transactionItems.product.category', 'user']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_revenue' => $transactions->sum('total_amount'),
            'transactions_by_status' => $transactions->groupBy('status')->map->count(),
            'top_products' => $this->getTopProducts($transactions),
            'revenue_by_date' => $transactions->groupBy(fn($t) => $t->created_at->format('Y-m-d'))
                ->map(fn($group) => $group->sum('total_amount')),
        ];
    }

    /**
     * Get top selling products from transactions
     */
    private function getTopProducts(Collection $transactions): Collection
    {
        $productSales = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->transactionItems as $item) {
                $productId = $item->product_id;
                $productName = $item->product ? $item->product->name : 'Deleted Product';
                
                if (!$productSales->has($productId)) {
                    $productSales->put($productId, [
                        'product_id' => $productId,
                        'product_name' => $productName,
                        'total_quantity' => 0,
                        'total_revenue' => 0,
                    ]);
                }

                $productSales[$productId]['total_quantity'] += $item->quantity;
                $productSales[$productId]['total_revenue'] += $item->subtotal;
            }
        }

        return $productSales->sortByDesc('total_quantity')->take(10);
    }
}