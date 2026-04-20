<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ANG IYONG PERSONAL ADMIN ACCOUNT
        User::create([
            'first_name' => 'Jose',
            'middle_name' => 'Angeles',
            'last_name' => 'Olinares',
            'suffix' => 'III',
            'sex' => 'Male',
            'date_of_birth' => '2003-12-08',
            'house_number' => '44',
            'purok_street' => 'Capalungan st',
            'contact_number' => '09458275591',
            'email' => 'joseolinares1443@gmail.com',
            'password' => Hash::make('Lookatme45'),
            'id_photo_path' => 'verification_ids/dummy_id.jpg',
            'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'admin',
            'contact_verified_at' => now(),
            'email_verified_at' => now(),
            'is_verified' => 1,
            'wants_email_notification' => 1,
            'terms_accepted_at' => now(),
            'signup_ip' => '127.0.0.1',
        ]);

        // 2. DUMMY RESIDENT ACCOUNT PARA MAY MA-TEST KA SA FRONTEND
        User::create([
            'first_name' => 'Mark',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'suffix' => null,
            'sex' => 'Male',
            'date_of_birth' => '1995-05-15',
            'house_number' => 'Block 1',
            'purok_street' => 'Purok 1',
            'contact_number' => '09000000000',
            'email' => 'markdela@gmail.com',
            'password' => Hash::make('password123'),
            'id_photo_path' => 'verification_ids/dummy_id.jpg',
            'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident',
            'contact_verified_at' => now(),
            'is_verified' => 1, // Verified na agad para makapag-request
            'wants_email_notification' => 0,
            'terms_accepted_at' => now(),
        ]);

        // 3. DUMMY RESIDENT ACCOUNT FOR UNREGISTERED
        User::create([
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'suffix' => 'Jr.',
            'sex' => 'Male',
            'date_of_birth' => '1995-05-15',
            'house_number' => '44',
            'purok_street' => 'Purok 2, Capalungan St.',
            'contact_number' => '09000000001',
            'email' => 'Juandela@bdls.gov.ph',
            'password' => Hash::make('password123'),
            'id_photo_path' => 'verification_ids/dummy_id.jpg',
            'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident',
            'contact_verified_at' => now(),
            'is_verified' => 0, // not verified
            'wants_email_notification' => 0,
            'terms_accepted_at' => now(),
        ]);
    }
}
