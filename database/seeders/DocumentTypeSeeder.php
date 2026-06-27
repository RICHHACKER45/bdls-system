<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    // This is where documents is seeded through
    public function run(): void
    {
        $documents = [
            ['name' => 'Barangay Certificate of Residency', 'reqs' => 'Valid ID', 'time' => 40],
            ['name' => 'Barangay Clearance o Good Moral', 'reqs' => 'Valid ID, Latest Community Tax Certificate (CTC)', 'time' => 30],
            ['name' => 'Certification para sa Senior Citizen', 'reqs' => 'Valid ID', 'time' => 50],
            ['name' => 'Certification para sa Solo Parent', 'reqs' => 'Valid ID', 'time' => 50],
            ['name' => 'Certificate of Indigency', 'reqs' => 'Valid ID', 'time' => 50],
            ['name' => 'Pagpapatunay sa Hanapbuhay', 'reqs' => 'Valid ID', 'time' => 50],
            ['name' => 'Certificate of Non-Residence', 'reqs' => 'Valid ID, Pormal na kahilingan (pamahalaan)', 'time' => 50],
            ['name' => 'First Time Jobseekers Certification', 'reqs' => 'Valid ID, Birth certificate o PSA, Diploma o katunayan', 'time' => 60],
            ['name' => 'BARC Certification', 'reqs' => 'Valid ID, RSBSA Enrolment form o Titulo ng lupa', 'time' => 30],
            ['name' => 'Certificate of Low Income', 'reqs' => 'Valid ID', 'time' => 30],
            ['name' => 'Certification Co-Habitation', 'reqs' => 'Valid ID', 'time' => 50],
            ['name' => 'PWD Certification', 'reqs' => 'Valid ID', 'time' => 30],
            ['name' => 'Special Purpose Barangay Certification', 'reqs' => 'Valid ID', 'time' => 50],
        ];

        foreach ($documents as $doc) {
            DB::table('document_types')->insert([
                'name' => $doc['name'],
                'requirements_description' => $doc['reqs'],
                'processing_fee' => 0.00,
                'processing_time_minutes' => $doc['time'],
                'is_active' => 1, // Laging active by default
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
