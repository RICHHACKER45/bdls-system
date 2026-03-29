<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. GAWA TAYO NG ISANG ADMIN ACCOUNT
        User::create([
            'first_name' => 'Barangay',
            'last_name' => 'Admin',
            'date_of_birth' => '1990-01-01',
            'house_number' => '001',
            'purok_street' => 'Barangay Hall',
            'contact_number' => '09000000000',
            'email' => 'admin@bdls.gov.ph',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'is_verified' => 1,
        ]);

        // 2. GAWA TAYO NG 5 DUMMY RESIDENTS GAMIT ANG FOR-LOOP
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'first_name' => 'Resident',
                'last_name' => 'Test ' . $i,
                'date_of_birth' => '2000-01-0' . $i,
                'house_number' => 'Block ' . $i,
                'purok_street' => 'Purok ' . $i,
                'contact_number' => '0911111111' . $i,
                'email' => 'resident' . $i . '@test.com',
                'password' => Hash::make('password123'),
                'role' => 'resident',
                'is_verified' => 1, // Naka-verify na agad para makapag-test ka ng service requests!
            ]);
        }
    }
}