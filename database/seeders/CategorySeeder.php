<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Latest electronic gadgets and accessories',
                'slug' => 'electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashionable clothing for all seasons',
                'slug' => 'clothing',
                'is_active' => true,
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Everything you need for your home and garden',
                'slug' => 'home-garden',
                'is_active' => true,
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Equipment for sports and outdoor activities',
                'slug' => 'sports-outdoors',
                'is_active' => true,
            ],
            [
                'name' => 'Books & Media',
                'description' => 'Books, movies, music and more',
                'slug' => 'books-media',
                'is_active' => true,
            ],
            [
                'name' => 'Toys & Games',
                'description' => 'Fun and educational toys and games',
                'slug' => 'toys-games',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Categories seeded successfully!');
    }
}
