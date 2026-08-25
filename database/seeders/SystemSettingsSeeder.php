<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pre-populate global contact & payment credentials
        \DB::table('system_settings')->insert([
            ['key' => 'gcash_number', 'value' => '09123456789', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maya_number', 'value' => '09123456789', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_phone', 'value' => '09000000002', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_email', 'value' => 'barangaysec@bdlsgov.ph', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Pre-populate core landing page contents
        \DB::table('landing_page_contents')->insert([
            [
                'section_name' => 'vision_mission',
                'content_data' => json_encode([
                    'vision' => 'Isang maunlad, ligtas, at nagkakaisang Barangay Doña Lucia na may tapat na paglilingkod.',
                    'mission' => 'Maghatid ng mabilis, maaasahan, at makabagong serbisyong pampubliko para sa lahat.',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
