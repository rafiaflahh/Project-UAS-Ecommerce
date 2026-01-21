<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@shophub.com',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
            ],
            [
                'name' => 'John Customer',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'role' => 'USER',
            ],
            [
                'name' => 'Jane Customer',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'role' => 'USER',
            ],
            [
                'name' => 'Bob Customer',
                'email' => 'bob@example.com',
                'password' => Hash::make('password123'),
                'role' => 'USER',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin credentials: admin@shophub.com / password123');
        $this->command->info('User credentials: john@example.com / password123');
    }
}