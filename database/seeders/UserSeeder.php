<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. OFFICIAL ACCOUNT: BARANGAY CAPTAIN (Admin)
        User::create([
            'first_name' => 'Punong',
            'middle_name' => '',
            'last_name' => 'Barangay',
            'suffix' => '',
            'sex' => 'Male',
            'date_of_birth' => '1970-01-01',
            'address' => 'Barangay Hall, Doña Lucia',
            'contact_number' => '09000000001',
            'email' => 'barangaycap@bdlsgov.ph',
            'password' => Hash::make('Admin12345!'),
            
            // 🛑 BINURA: id_photo_path at selfie_photo_path

            'role' => 'admin',
            'contact_verified_at' => now(),
            'email_verified_at' => now(),
            'is_verified' => 1,
            'wants_email_notification' => 1,
            'terms_accepted_at' => now(),
        ]);

        // 2. OFFICIAL ACCOUNT: BARANGAY SECRETARY (Admin)
        User::create([
            'first_name' => 'Barangay',
            'middle_name' => '',
            'last_name' => 'Secretary',
            'suffix' => '',
            'sex' => 'Female',
            'date_of_birth' => '1990-01-01',
            'address' => 'Barangay Hall, Doña Lucia',
            'contact_number' => '09000000002',
            'email' => 'barangaysec@bdlsgov.ph',
            'password' => Hash::make('Admin12345!'),
            
            // 🛑 BINURA: id_photo_path at selfie_photo_path

            'role' => 'admin',
            'contact_verified_at' => now(),
            'email_verified_at' => now(),
            'is_verified' => 1,
            'wants_email_notification' => 1,
            'terms_accepted_at' => now(),
        ]);
    }
}