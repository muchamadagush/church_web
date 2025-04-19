<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'username' => 'admin1',
                'fullname' => 'Administrator One',
                'email' => 'admin1@church.com',
            ],
            [
                'username' => 'admin2',
                'fullname' => 'Administrator Two',
                'email' => 'admin2@church.com',
            ]
        ];

        foreach ($admins as $admin) {
            User::create([
                'username' => $admin['username'],
                'fullname' => $admin['fullname'],
                'email' => $admin['email'],
                'password' => 'Admin123',
                'role' => 'admin',
            ]);
        }
    }
}
