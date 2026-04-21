<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. OFFICIAL ACCOUNT: BARANGAY CAPTAIN (Admin)
        User::create([
            'first_name' => 'Punong',
            'middle_name' => '',
            'last_name' => 'Barangay', // Pwede mong palitan ng totoong apelyido ni Kapitan bukas
            'suffix' => '',
            'sex' => 'Male',
            'date_of_birth' => '1970-01-01',
            'house_number' => 'Barangay Hall',
            'purok_street' => 'Doña Lucia',
            'contact_number' => '09000000001',
            'email' => 'barangaycap@bdlsgov.ph',
            'password' => Hash::make('Admin12345!'), // Ligtas na default password
            'id_photo_path' => 'verification_ids/dummy_id.jpg',
            'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
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
            'last_name' => 'Secretary', // Pwede mong palitan ng totoong apelyido ni Sec bukas
            'suffix' => '',
            'sex' => 'Female',
            'date_of_birth' => '1990-01-01',
            'house_number' => 'Barangay Hall',
            'purok_street' => 'Doña Lucia',
            'contact_number' => '09000000002',
            'email' => 'barangaysec@bdlsgov.ph',
            'password' => Hash::make('Admin12345!'),
            'id_photo_path' => 'verification_ids/dummy_id.jpg',
            'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'admin',
            'contact_verified_at' => now(),
            'email_verified_at' => now(),
            'is_verified' => 1,
            'wants_email_notification' => 1,
            'terms_accepted_at' => now(),
        ]);
    }
}