<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing users
        User::truncate();
        
        // Create admin user
        User::create([
            'username' => 'admin',
            'fullname' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin123'),
            'role' => 'admin'
        ]);
    }
}
