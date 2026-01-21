<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\TransactionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseCleaner extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting database cleanup...');
        
        // Remove transaction items first (no dependencies)
        $this->command->info('Removing transaction items...');
        TransactionItem::truncate();
        
        // Remove product stocks first (depends on products)
        $this->command->info('Removing product stocks...');
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        ProductStock::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
        // Remove products (main table)
        $this->command->info('Removing products...');
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
        // Clear any caches
        $this->command->info('Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        $this->command->info('Database cleanup completed!');
        $this->command->info('System is now ready for clean product setup by admins only.');
        $this->command->info('Foreign key constraints temporarily disabled for safe truncation.');
    }
}