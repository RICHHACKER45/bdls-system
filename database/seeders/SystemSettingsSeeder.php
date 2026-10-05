<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'gcash_name'],
            ['value' => 'Barangay Doña Lucia Treasurer']
        );

        SystemSetting::updateOrCreate(
            ['key' => 'gcash_number'],
            ['value' => '09123456789']
        );

        SystemSetting::updateOrCreate(
            ['key' => 'gcash_qr_path'],
            ['value' => null]
        );
    }
}
