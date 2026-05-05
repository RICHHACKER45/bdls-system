<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tinatawag natin dito yung ginawa nating UserSeeder
        $this->call([UserSeeder::class, DocumentTypeSeeder::class, TestSeeder::class]);
    }
}
