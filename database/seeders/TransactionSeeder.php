<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only run if we have products and users
        if (Product::count() === 0 || User::count() === 0) {
            $this->command->info('Skipping TransactionSeeder - no products or users found');
            return;
        }

        $users = User::where('role', 'USER')->get();
        $products = Product::with('stock')->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->info('Skipping TransactionSeeder - no eligible users or products found');
            return;
        }

        // Create sample transactions with proper database consistency
        DB::transaction(function () use ($users, $products) {
            for ($i = 0; $i < 20; $i++) {
                // Random user
                $user = $users->random();
                
                // Random number of items (1-3 per transaction)
                $numItems = rand(1, 3);
                $selectedProducts = $products->random($numItems);
                $totalAmount = 0;
                
                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_number' => 'TEST-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'total_amount' => 0, // Will be calculated
                    'status' => ['COMPLETED', 'PROCESSING', 'PENDING'][rand(0, 2)],
                    'notes' => rand(0, 1) ? 'Sample transaction note' . $i : null,
                ]);

                // Create transaction items and update stock
                $transactionItems = [];
                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, min(3, $product->stock->available_quantity));
                    
                    if ($quantity > 0) {
                        $unitPrice = $product->price;
                        $subtotal = $unitPrice * $quantity;
                        $totalAmount += $subtotal;

                        $transactionItems[] = [
                            'transaction_id' => $transaction->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Update stock for completed transactions
                        if ($transaction->status === 'COMPLETED') {
                            $product->stock->quantity -= $quantity;
                            $product->stock->save();
                        }
                    }
                }

                // Insert transaction items
                if (!empty($transactionItems)) {
                    TransactionItem::insert($transactionItems);
                    
                    // Update transaction total
                    $transaction->update(['total_amount' => $totalAmount]);
                    
                    // Random creation time
                    $transaction->created_at = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                    $transaction->save();
                } else {
                    // Delete empty transaction
                    $transaction->delete();
                }
            }
        });

        $this->command->info('Created sample transactions with database consistency');
    }
}
