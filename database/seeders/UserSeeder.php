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
        User::updateOrCreate(
            ['email' => 'barangaycap@bdlsgov.ph'],
            [
                'first_name' => 'Punong',
                'middle_name' => '',
                'last_name' => 'Barangay',
                'suffix' => '',
                'sex' => 'Male',
                'date_of_birth' => '1970-01-01',
                'address' => 'Barangay Hall, Doña Lucia',
                'contact_number' => '09000000001',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
                'contact_verified_at' => now(),
                'email_verified_at' => now(),
                'is_verified' => 1,
                'wants_email_notification' => 1,
                'terms_accepted_at' => now(),
            ]
        );

        // 2. OFFICIAL ACCOUNT: BARANGAY SECRETARY (Admin)
        User::updateOrCreate(
            ['email' => 'barangaysec@bdlsgov.ph'],
            [
                'first_name' => 'Barangay',
                'middle_name' => '',
                'last_name' => 'Secretary',
                'suffix' => '',
                'sex' => 'Female',
                'date_of_birth' => '1990-01-01',
                'address' => 'Barangay Hall, Doña Lucia',
                'contact_number' => '09000000002',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
                'contact_verified_at' => now(),
                'email_verified_at' => now(),
                'is_verified' => 1,
                'wants_email_notification' => 1,
                'terms_accepted_at' => now(),
            ]
        );

        // 3. OFFICIAL ACCOUNT: RESIDENT (PRE-VERIFIED)
        User::updateOrCreate(
            ['email' => 'resident@gmail.com'],
            [
                'first_name' => 'Resident',
                'middle_name' => '',
                'last_name' => 'User',
                'suffix' => '',
                'sex' => 'Male',
                'date_of_birth' => '1995-01-01',
                'address' => '44 Capalungan St. Brgy Dona Lucia Q.N.E',
                'contact_number' => '0999999991',
                'password' => Hash::make('User12345!'),
                'role' => 'resident',
                'contact_verified_at' => now(),
                'email_verified_at' => now(),
                'is_verified' => 1,
                'wants_email_notification' => 1,
                'terms_accepted_at' => now(),
            ]
        );
    }
}
