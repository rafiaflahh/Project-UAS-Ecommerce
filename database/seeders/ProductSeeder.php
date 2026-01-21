<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');
        
        $products = [
            // Electronics
            [
                'name' => 'Wireless Headphones Pro',
                'description' => 'Premium wireless headphones with noise cancellation and 30-hour battery life.',
                'price' => 199.99,
                'cost_price' => 120.00,
                'sku' => 'WHP-001',
                'category_id' => $categories['electronics']->id,
                'is_active' => true,
                'quantity' => 50,
            ],
            [
                'name' => 'Smartphone 6.5"',
                'description' => 'Latest smartphone with advanced camera system and all-day battery.',
                'price' => 699.99,
                'cost_price' => 450.00,
                'sku' => 'SPH-002',
                'category_id' => $categories['electronics']->id,
                'is_active' => true,
                'quantity' => 25,
            ],
            [
                'name' => 'Laptop Ultra',
                'description' => 'High-performance laptop with 16GB RAM and 1TB SSD.',
                'price' => 1299.99,
                'cost_price' => 900.00,
                'sku' => 'LAP-003',
                'category_id' => $categories['electronics']->id,
                'is_active' => true,
                'quantity' => 15,
            ],
            
            // Clothing
            [
                'name' => 'Premium Cotton T-Shirt',
                'description' => 'Comfortable 100% cotton t-shirt in various colors.',
                'price' => 29.99,
                'cost_price' => 12.00,
                'sku' => 'TSH-001',
                'category_id' => $categories['clothing']->id,
                'is_active' => true,
                'quantity' => 100,
            ],
            [
                'name' => 'Denim Jeans Classic',
                'description' => 'Classic fit denim jeans with premium quality fabric.',
                'price' => 79.99,
                'cost_price' => 35.00,
                'sku' => 'JNS-002',
                'category_id' => $categories['clothing']->id,
                'is_active' => true,
                'quantity' => 60,
            ],
            
            // Home & Garden
            [
                'name' => 'Coffee Maker Deluxe',
                'description' => 'Automatic coffee maker with built-in grinder and timer.',
                'price' => 149.99,
                'cost_price' => 80.00,
                'sku' => 'CMK-001',
                'category_id' => $categories['home-garden']->id,
                'is_active' => true,
                'quantity' => 30,
            ],
            [
                'name' => 'Garden Tool Set',
                'description' => 'Complete garden tool set for all your gardening needs.',
                'price' => 89.99,
                'cost_price' => 45.00,
                'sku' => 'GTS-002',
                'category_id' => $categories['home-garden']->id,
                'is_active' => true,
                'quantity' => 40,
            ],
            
            // Sports & Outdoors
            [
                'name' => 'Yoga Mat Professional',
                'description' => 'Extra thick yoga mat with alignment lines.',
                'price' => 49.99,
                'cost_price' => 25.00,
                'sku' => 'YGM-001',
                'category_id' => $categories['sports-outdoors']->id,
                'is_active' => true,
                'quantity' => 75,
            ],
            [
                'name' => 'Running Shoes Elite',
                'description' => 'Professional running shoes with advanced cushioning.',
                'price' => 129.99,
                'cost_price' => 70.00,
                'sku' => 'RSH-001',
                'category_id' => $categories['sports-outdoors']->id,
                'is_active' => true,
                'quantity' => 35,
            ],
            
            // Books & Media
            [
                'name' => 'Programming Best Practices',
                'description' => 'Essential guide to modern programming techniques.',
                'price' => 39.99,
                'cost_price' => 20.00,
                'sku' => 'BKM-001',
                'category_id' => $categories['books-media']->id,
                'is_active' => true,
                'quantity' => 200,
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'Compact wireless earbuds with premium sound quality.',
                'price' => 89.99,
                'cost_price' => 40.00,
                'sku' => 'WEB-002',
                'category_id' => $categories['books-media']->id,
                'is_active' => true,
                'quantity' => 85,
            ],
            
            // Toys & Games
            [
                'name' => 'Educational Puzzle Set',
                'description' => 'Fun puzzle set that promotes cognitive development.',
                'price' => 34.99,
                'cost_price' => 15.00,
                'sku' => 'PZL-001',
                'category_id' => $categories['toys-games']->id,
                'is_active' => true,
                'quantity' => 45,
            ],
            [
                'name' => 'Board Game Collection',
                'description' => 'Classic board games for family entertainment.',
                'price' => 44.99,
                'cost_price' => 22.00,
                'sku' => 'BGM-002',
                'category_id' => $categories['toys-games']->id,
                'is_active' => true,
                'quantity' => 60,
            ],
        ];

        foreach ($products as $productData) {
            // Create product without image_url initially
            $product = Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'cost_price' => $productData['cost_price'],
                'sku' => $productData['sku'],
                'category_id' => $productData['category_id'],
                'is_active' => $productData['is_active'],
            ]);

            // Create stock for each product
            ProductStock::create([
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'reserved_quantity' => 0,
            ]);
        }

        $this->command->info('Products seeded successfully!');
    }
}