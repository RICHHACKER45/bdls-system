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
            ['name' => 'Barangay Certificate of Residency', 'reqs' => 'Valid ID'],
            [
                'name' => 'Barangay Clearance o Good Moral',
                'reqs' => 'Valid ID, Latest Community Tax Certificate (CTC)',
            ],
            ['name' => 'Certification para sa Senior Citizen', 'reqs' => 'Valid ID'],
            ['name' => 'Certification para sa Solo Parent', 'reqs' => 'Valid ID'],
            ['name' => 'Certificate of Indigency', 'reqs' => 'Valid ID'],
            ['name' => 'Pagpapatunay sa Hanapbuhay', 'reqs' => 'Valid ID'],
            [
                'name' => 'Certificate of Non-Residence',
                'reqs' => 'Valid ID, Pormal na kahilingan (pamahalaan)',
            ],
            [
                'name' => 'First Time Jobseekers Certification',
                'reqs' => 'Valid ID, Birth certificate o PSA, Diploma o katunayan',
            ],
            [
                'name' => 'BARC Certification',
                'reqs' => 'Valid ID, RSBSA Enrolment form o Titulo ng lupa',
            ],
            ['name' => 'Certificate of Low Income', 'reqs' => 'Valid ID'],
            ['name' => 'Certification Co-Habitation', 'reqs' => 'Valid ID'],
            ['name' => 'PWD Certification', 'reqs' => 'Valid ID'],
            ['name' => 'Special Purpose Barangay Certification', 'reqs' => 'Valid ID'],
        ];

        foreach ($documents as $doc) {
            DB::table('document_types')->insert([
                'name' => $doc['name'],
                'requirements_description' => $doc['reqs'],
                'is_active' => 1, // Laging active by default
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
