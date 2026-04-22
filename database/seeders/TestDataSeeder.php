<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $documents = DocumentType::pluck('id')->toArray();

        if (empty($documents)) {
            $this->command->error('Error: Patakbuhin muna ang DocumentTypeSeeder!');
            return;
        }

        $this->command->info('Gumagawa ng 50 Fake Residents...');

        $residents = [];
        for ($i = 0; $i < 50; $i++) {
            $residents[] = User::create([
                // The Laravel Way: Localized Fake Data (en_PH)
                'first_name' => fake('en_PH')->firstName(),
                'last_name' => fake('en_PH')->lastName(),
                'sex' => fake()->randomElement(['Male', 'Female']),
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'house_number' => fake('en_PH')->buildingNumber(),
                'purok_street' => 'Purok ' . fake()->numberBetween(1, 7),
                // Ligtas sa Unique Constraint Constraint
                'contact_number' => fake()->unique()->numerify('09#########'),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password123'),
                'id_photo_path' => 'verification_ids/dummy_id.jpg',
                'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
                'role' => 'resident',
                'contact_verified_at' => now(),
                'is_verified' => fake()->boolean(85), // 85% chance na approved na sila
                'terms_accepted_at' => now(),
            ]);
        }

        $this->command->info('Gumagawa ng 150 Fake Service Requests...');

        $statuses = ['pending', 'processing', 'for_interview', 'released', 'received', 'rejected', 'canceled'];
        $onlineCount = 1;
        $walkinCount = 1;

        foreach ($residents as $user) {
            // Bigyan ng 1 hanggang 4 na requests ang bawat tao
            $requestCount = fake()->numberBetween(1, 4);
            
            for ($j = 0; $j < $requestCount; $j++) {
                $channel = fake()->randomElement(['Online', 'Walk-in']);
                $status = fake()->randomElement($statuses);
                
                if ($channel === 'Online') {
                    $queueNumber = 'O-' . str_pad($onlineCount++, 3, '0', STR_PAD_LEFT);
                } else {
                    $queueNumber = 'W-' . str_pad($walkinCount++, 3, '0', STR_PAD_LEFT);
                }

                ServiceRequest::create([
                    'user_id' => $user->id,
                    'document_type_id' => fake()->randomElement($documents),
                    'request_channel' => $channel,
                    'queue_number' => $queueNumber,
                    'purpose' => fake()->sentence(4),
                    'preferred_pickup_time' => now()->addDays(fake()->numberBetween(1, 5)),
                    'status' => $status,
                    'released_at' => in_array($status, ['released', 'received']) ? now()->subHours(fake()->numberBetween(1, 48)) : null,
                    // I-backdate ang created_at para lumabas nang maganda sa Analytics PDF
                    'created_at' => now()->subDays(fake()->numberBetween(1, 30)),
                    'updated_at' => now()->subDays(fake()->numberBetween(0, 30)),
                ]);
            }
        }

        $this->command->info('SUKSES! Handang-handa na ang system mo para sa stress test.');
    }
}