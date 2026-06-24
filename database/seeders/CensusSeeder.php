<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CensusSeeder extends Seeder
{
    public function run(): void
    {
        $residents = [
            // 1. MGA TEST ACCOUNTS NATIN (Para mag-match sa OCR Testing mo mamaya)
            [
                'first_name' => 'Juan', 'middle_name' => 'Bautista', 'last_name' => 'Dela Cruz',
                'suffix' => null, 'sex' => 'Male', 'date_of_birth' => '1990-05-15',
                'address' => '123 Purok 1, Brgy. Doña Lucia', 'is_alive' => 1,
            ],
            [
                'first_name' => 'Pedro', 'middle_name' => 'Santos', 'last_name' => 'Penduko',
                'suffix' => null, 'sex' => 'Male', 'date_of_birth' => '1985-10-20',
                'address' => '456 Purok 2, Brgy. Doña Lucia', 'is_alive' => 1,
            ],
            [
                'first_name' => 'Maria', 'middle_name' => 'Perez', 'last_name' => 'Clara',
                'suffix' => null, 'sex' => 'Female', 'date_of_birth' => '1998-08-20',
                'address' => '789 Purok 3, Brgy. Doña Lucia', 'is_alive' => 1,
            ],

            // 2. RANDOM CENSUS DATA (Para kunwari totoong barangay database)
            [
                'first_name' => 'Jose', 'middle_name' => 'A', 'last_name' => 'Olinares',
                'suffix' => 'III', 'sex' => 'Male', 'date_of_birth' => '2003-12-08', // Example mo
                'address' => 'Purok 2, Brgy. Doña Lucia', 'is_alive' => 1,
            ],
            [
                'first_name' => 'Beatriz', 'middle_name' => 'D', 'last_name' => 'Villamia',
                'suffix' => null, 'sex' => 'Female', 'date_of_birth' => '2003-05-12',
                'address' => 'Purok 5, Brgy. Doña Lucia', 'is_alive' => 1,
            ],
            [
                'first_name' => 'Maricar', 'middle_name' => 'F', 'last_name' => 'Gregorio',
                'suffix' => null, 'sex' => 'Female', 'date_of_birth' => '2002-09-08',
                'address' => 'Purok 6, Brgy. Doña Lucia', 'is_alive' => 1,
            ],
            [
                'first_name' => 'Ana Leah', 'middle_name' => 'D', 'last_name' => 'Marcelo',
                'suffix' => null, 'sex' => 'Female', 'date_of_birth' => '2003-11-25',
                'address' => 'Purok 7, Brgy. Doña Lucia', 'is_alive' => 1,
            ],
        ];

        foreach ($residents as $resident) {
            $resident['created_at'] = now();
            $resident['updated_at'] = now();
            DB::table('census_records')->insert($resident);
        }
    }
}
