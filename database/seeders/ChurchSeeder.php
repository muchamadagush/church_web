<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChurchSeeder extends Seeder
{
    public function run()
    {
        $churches = [
            ['name' => 'GGP Bukit Zaitun Kole'],
            ['name' => 'GGP Shalom Ne`Me`Se'],
            ['name' => 'GGP Solagratia Tiroan'],
            ['name' => 'GGP El-Shadday Ratte'],
            ['name' => 'GGP Benteng Batu'],
            ['name' => 'GGP Getsemani Bu`Buk'],
            ['name' => 'GGP Anugrah Salu Baruppu`'],
            ['name' => 'GGP Salurea'],
            ['name' => 'GGP Pa`Kappan'],
            ['name' => 'GGP Lembah Pujian To`Lemo'],
            ['name' => 'GGP Imanuel Ratte'],
        ];

        DB::table('churches')->insert($churches);
    }
}
