<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Church;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ChurchGembalaSeeder extends Seeder
{
    public function run(): void
    {
        $churches = Church::all();

        foreach ($churches as $church) {
            for ($i = 1; $i <= 3; $i++) {
                $churchSlug = strtolower(str_replace(' ', '', $church->name));
                User::create([
                    'username' => "gembala{$i}.{$churchSlug}",
                    'fullname' => "Gembala {$i} - {$church->name}",
                    'email' => "gembala{$i}.{$churchSlug}@church.com",
                    'password' => 'password',
                    'church_id' => $church->id,
                    'role' => 'gembala',
                ]);
            }
        }
    }
}
