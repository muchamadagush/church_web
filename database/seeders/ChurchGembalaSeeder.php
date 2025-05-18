<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Church;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChurchGembalaSeeder extends Seeder
{
    public function run(): void
    {
        // Define mapping of church names to gembala names
        $gembalas = [
            'GGP Bukit Zaitun Kole' => 'Pdt. Daniel Johni, S.Th',
            'GGP Shalom Ne`Me`Se' => 'Pdt. Orva, S.Pd',
            'GGP Solagratia Tiroan' => 'Pdp. Matius Leppang',
            'GGP El-Shadday Ratte' => 'Pdp. Yuni Datu Maling',
            'GGP Benteng Batu' => 'Pdt. Thomas Tappi`',
            'GGP Getsemani Bu`Buk' => 'Pdm. Andarias Minggu',
            'GGP Anugrah Salu Baruppu`' => 'Pdt. Semuel Soni`, S.Th',
            'GGP Salurea' => 'Pdt. Andarias Layuk Langi`, S.Th',
            'GGP Pa`Kappan' => 'Ibu Elisabeth Toding Mangatta',
            'GGP Lembah Pujian To`Lemo' => 'Pdm. Mesakh Bennu, S.Th',
            'GGP Imanuel Ratte' => 'Ibu Rina Tappi`'
        ];

        $churches = Church::all();

        foreach ($churches as $church) {
            // Find the gembala name for this church
            $gembalaName = $gembalas[$church->name] ?? 'Gembala ' . $church->name;
            
            // Generate username and email in the original format
            $churchSlug = strtolower(str_replace(' ', '', $gembalas[$church->name]));
            $username = $churchSlug;
            $email = "gembala." . $churchSlug . "@church.com";
            
            // Create the gembala user
            User::create([
                'username' => $username,
                'fullname' => $gembalaName,
                'email' => $email,
                'password' => 'password',
                'church_id' => $church->id,
                'role' => 'gembala',
            ]);
        }
    }
}
