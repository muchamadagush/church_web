<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChurchSeeder extends Seeder
{
    public function run()
    {
        $churches = [
            ['name' => 'GGP Bukit Zaitun Kde'],
            ['name' => 'GGP Shalom Ne`Me`se'],
            ['name' => 'GGP Solaqraya Tiroan'],
            ['name' => 'GGP El-Shadday Ratte'],
            ['name' => 'GGP Benteng Batu'],
            ['name' => 'GGP Getsemani Bu`Buk'],
            ['name' => 'GGP Anugrah Salu Baruppu`'],
            ['name' => 'GGP Salurea'],
            ['name' => 'GGP Pa`Kappuan'],
            ['name' => 'GGP Lembah Pujian To`Lemo'],
            ['name' => 'GGP Immanuel Ratte'],
        ];

        DB::table('churches')->insert($churches);
    }
}
