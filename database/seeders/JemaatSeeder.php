<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Church;
use Carbon\Carbon;

class JemaatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all churches to match names
        $churches = Church::all()->pluck('id', 'name')->toArray();

        // Array of jemaat data from the provided file
        $jemaatData = [
            // GGP BUKIT ZAITUN KOLE
            ["name" => "Daniel Johni", "birthplace" => "Baruppu'", "dateofbirth" => "17-07-1975", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "kepala_keluarga"],
            ["name" => "Dina Kondo", "birthplace" => "Riu", "dateofbirth" => "01-01-1976", "gender" => "P", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "istri"],
            ["name" => "Semuel Joni", "birthplace" => "Baruppu'", "dateofbirth" => "19-02-2000", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Jessica Daniel Joni", "birthplace" => "Baruppu'", "dateofbirth" => "13-09-2001", "gender" => "P", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "David Daniel", "birthplace" => "Baruppu'", "dateofbirth" => "14-08-2003", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Josua Daniel Johni", "birthplace" => "Baruppu'", "dateofbirth" => "14-04-2008", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Gloryana Jhoni", "birthplace" => "Baruppu'", "dateofbirth" => "05-05-2018", "gender" => "P", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Marten Duma'", "birthplace" => "Baruppu'", "dateofbirth" => "30-03-1986", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "kepala_keluarga"],
            ["name" => "Melika Debora", "birthplace" => "Baruppu'", "dateofbirth" => "07-05-1994", "gender" => "P", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "istri"],
            ["name" => "Melki Revandi Marten", "birthplace" => "Baruppu'", "dateofbirth" => "15-07-2011", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Milvi Septiani Marten", "birthplace" => "Baruppu'", "dateofbirth" => "03-09-2015", "gender" => "P", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Mirza Anggita Marten", "birthplace" => "Baruppu'", "dateofbirth" => "12-08-2018", "gender" => "P", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            ["name" => "Mikhael Saputra Marten", "birthplace" => "Toraja Utara", "dateofbirth" => "09-04-2023", "gender" => "L", "church" => "GGP BUKIT ZAITUN KOLE", "family_status" => "anak"],
            
            // GGP SHALOM NE'ME'SE
            ["name" => "Simon Sampe Limbong", "birthplace" => "Makassar", "dateofbirth" => "13-12-1962", "gender" => "L", "church" => "GGP SHALOM NE'ME'SE", "family_status" => "kepala_keluarga"],
            ["name" => "Orva", "birthplace" => "Baruppu'", "dateofbirth" => "24-10-1966", "gender" => "P", "church" => "GGP SHALOM NE'ME'SE", "family_status" => "istri"],
            ["name" => "Deri Simon", "birthplace" => "Baruppu'", "dateofbirth" => "17-12-1994", "gender" => "L", "church" => "GGP SHALOM NE'ME'SE", "family_status" => "anak"],
            ["name" => "Agustina Simon", "birthplace" => "Baruppu'", "dateofbirth" => "20-08-1998", "gender" => "P", "church" => "GGP SHALOM NE'ME'SE", "family_status" => "anak"],
            ["name" => "Olvian Simon", "birthplace" => "Baruppu'", "dateofbirth" => "18-10-2001", "gender" => "L", "church" => "GGP SHALOM NE'ME'SE", "family_status" => "anak"],
            
            // GGP EL-SHADDAY RATTE
            ["name" => "Antonius Langgu", "birthplace" => "Ratte", "dateofbirth" => "04-04-1973", "gender" => "L", "church" => "GGP EL-SHADDAY RATTE", "family_status" => "kepala_keluarga"],
            ["name" => "Yuni Datu Maling", "birthplace" => "Ratte", "dateofbirth" => "06-06-1974", "gender" => "P", "church" => "GGP EL-SHADDAY RATTE", "family_status" => "istri"],
            ["name" => "Iren Anton", "birthplace" => "Ratte", "dateofbirth" => "31-03-2005", "gender" => "P", "church" => "GGP EL-SHADDAY RATTE", "family_status" => "anak"],
            ["name" => "Darwis Anton", "birthplace" => "Ratte", "dateofbirth" => "20-04-2008", "gender" => "L", "church" => "GGP EL-SHADDAY RATTE", "family_status" => "anak"],
            
            // GGP SOLAGRATIA TIROAN
            ["name" => "Matius Leppang", "birthplace" => "Tiroan", "dateofbirth" => "15-08-1975", "gender" => "L", "church" => "GGP SOLAGRATIA TIROAN", "family_status" => "kepala_keluarga"],
            ["name" => "Meliana Palengka", "birthplace" => "Mengkendek", "dateofbirth" => "13-12-1984", "gender" => "P", "church" => "GGP SOLAGRATIA TIROAN", "family_status" => "istri"],
            ["name" => "Kelvin Leonard", "birthplace" => "Tiroan", "dateofbirth" => "01-07-2003", "gender" => "L", "church" => "GGP SOLAGRATIA TIROAN", "family_status" => "anak"],
            ["name" => "Gabriela Faytsa Christia", "birthplace" => "Tiroan", "dateofbirth" => "12-03-2006", "gender" => "P", "church" => "GGP SOLAGRATIA TIROAN", "family_status" => "anak"],
            
            // GGP BENTENG BATU
            ["name" => "Thomas Tappi", "birthplace" => "Batu", "dateofbirth" => "21-12-1944", "gender" => "L", "church" => "GGP BENTENG BATU", "family_status" => "kepala_keluarga"],
            ["name" => "Maria", "birthplace" => "Batu", "dateofbirth" => "27-07-1958", "gender" => "P", "church" => "GGP BENTENG BATU", "family_status" => "istri"],
            ["name" => "Nopen", "birthplace" => "Batu", "dateofbirth" => "23-11-1993", "gender" => "L", "church" => "GGP BENTENG BATU", "family_status" => "anak"],
            
            // GGP GETSEMANI BU'BUK
            ["name" => "Andareas Minggu", "birthplace" => "Tiroan", "dateofbirth" => "22-12-1984", "gender" => "L", "church" => "GGP GETSEMANI BU'BUK", "family_status" => "kepala_keluarga"],
            ["name" => "Saria Talla", "birthplace" => "Ratte", "dateofbirth" => "11-09-1987", "gender" => "P", "church" => "GGP GETSEMANI BU'BUK", "family_status" => "istri"],
            ["name" => "Shintia Rani", "birthplace" => "Tiroan", "dateofbirth" => "18-01-2006", "gender" => "P", "church" => "GGP GETSEMANI BU'BUK", "family_status" => "anak"],
            
            // GGP ANUGRAH SALU BARUPPU'
            ["name" => "Semuel Soni'", "birthplace" => "Baruppu'", "dateofbirth" => "04-03-1974", "gender" => "L", "church" => "GGP ANUGRAH SALU BARUPPU'", "family_status" => "kepala_keluarga"],
            ["name" => "Martha Kala", "birthplace" => "Salu Baruppu'", "dateofbirth" => "09-08-1975", "gender" => "P", "church" => "GGP ANUGRAH SALU BARUPPU'", "family_status" => "istri"],
            ["name" => "Josua Kala'", "birthplace" => "Salu Baruppu'", "dateofbirth" => "24-05-2001", "gender" => "P", "church" => "GGP ANUGRAH SALU BARUPPU'", "family_status" => "anak"],
            
            // GGP SALUREA
            ["name" => "Andarias Layuk Langi'", "birthplace" => "Simbuang", "dateofbirth" => "12-07-1979", "gender" => "L", "church" => "GGP SALUREA", "family_status" => "kepala_keluarga"],
            ["name" => "Alfrida Petrus Bunga'", "birthplace" => "Baruppu'", "dateofbirth" => "11-04-1985", "gender" => "P", "church" => "GGP SALUREA", "family_status" => "istri"],
            ["name" => "Jitro Layuk Langi'", "birthplace" => "Baruppu'", "dateofbirth" => "30-03-2001", "gender" => "L", "church" => "GGP SALUREA", "family_status" => "anak"],
            
            // GGP LEMBAH PUJIAN TO'LEMO (already in the seeder)
            ["name" => "Mesakh Bennu", "birthplace" => "Soe", "dateofbirth" => "10-03-1989", "gender" => "L", "church" => "GGP LEMBAH PUJIAN TO'LEMO", "family_status" => "kepala_keluarga"],
            ["name" => "Naomi Batto Palungan", "birthplace" => "Tiroan", "dateofbirth" => "11-12-1987", "gender" => "P", "church" => "GGP LEMBAH PUJIAN TO'LEMO", "family_status" => "istri"],
            ["name" => "Alvian Rivaldo B", "birthplace" => "Toraja", "dateofbirth" => "15-04-2012", "gender" => "L", "church" => "GGP LEMBAH PUJIAN TO'LEMO", "family_status" => "anak"],
            
            // GGP IMANUEL RATTE
            ["name" => "Sini Bunga'", "birthplace" => "Baruppu'", "dateofbirth" => "14-03-1978", "gender" => "L", "church" => "GGP IMANUEL RATTE", "family_status" => "kepala_keluarga"],
            ["name" => "Rina Tappi'", "birthplace" => "Baruppu'", "dateofbirth" => "19-03-1983", "gender" => "P", "church" => "GGP IMANUEL RATTE", "family_status" => "istri"],
            ["name" => "Yonatan Leppang", "birthplace" => "Baruppu'", "dateofbirth" => "11-11-1999", "gender" => "L", "church" => "GGP IMANUEL RATTE", "family_status" => "anak"],
            
            // GGP PA'KAPPAN
            ["name" => "Suleman Parrangan", "birthplace" => "Baruppu'", "dateofbirth" => "31-12-1982", "gender" => "L", "church" => "GGP PA'KAPPAN", "family_status" => "kepala_keluarga"],
            ["name" => "Elisabeth Toding Mangatta", "birthplace" => "Tana Toraja", "dateofbirth" => "21-12-1986", "gender" => "P", "church" => "GGP PA'KAPPAN", "family_status" => "istri"],
            ["name" => "Palayukan", "birthplace" => "Madandan", "dateofbirth" => "15-05-2004", "gender" => "L", "church" => "GGP PA'KAPPAN", "family_status" => "anak"],
        ];

        // Process and insert the data
        $this->processAndInsertJemaatData($jemaatData, $churches);
    }

    /**
     * Process and insert jemaat data into database
     *
     * @param array $jemaatData
     * @param array $churches
     */
    private function processAndInsertJemaatData($jemaatData, $churches)
    {
        foreach ($jemaatData as $data) {
            // Find matching church
            $churchId = $this->findChurchId($churches, $data['church']);
            
            // Generate a simple username from full name (replace spaces with periods and lowercase)
            $username = strtolower(str_replace(' ', '.', $data['name']));
            
            // Format the date from dd-mm-yyyy to yyyy-mm-dd for database
            $dateOfBirth = Carbon::createFromFormat('d-m-Y', $data['dateofbirth'])->format('Y-m-d');
            
            // Convert gender format from L/P to male/female
            $gender = $data['gender'] === 'L' ? 'male' : 'female';
            
            // Insert the jemaat record
            DB::table('users')->insert([
                'username' => $username,
                'fullname' => $data['name'],
                'email' => $username . '@example.com', // Generate a placeholder email
                'password' => Hash::make('password'), // Default password
                'dateofbirth' => $dateOfBirth,
                'birthplace' => $data['birthplace'],
                'gender' => $gender,
                'family_status' => $data['family_status'],
                'address' => 'Toraja Utara', // Default address
                'church_id' => $churchId,
                'role' => 'jemaat', // Set role as jemaat
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Find the church ID based on the name or partial match.
     *
     * @param array $churches
     * @param string $churchName
     * @return int|null
     */
    private function findChurchId($churches, $churchName)
    {
        // Try exact match first
        foreach ($churches as $name => $id) {
            if ($name === $churchName) {
                return $id;
            }
        }

        // Try partial match
        foreach ($churches as $name => $id) {
            if (stripos($name, $churchName) !== false || stripos($churchName, $name) !== false) {
                return $id;
            }
        }

        // If no match is found, use the first church as default
        return reset($churches) ?: 1;
    }
}
