<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Support\Str;

class ServiceRequestTestSeeder extends Seeder
{
    public function run(): void
    {
        // Kunin lahat ng residente at dokumento sa database [1]
        $residents = User::where('role', 'resident')->get();
        $documents = DocumentType::all();
        $statuses = ['pending', 'processing', 'for_interview', 'released', 'received', 'rejected', 'canceled'];

        if ($residents->isEmpty() || $documents->isEmpty()) {
            $this->command->warn('Walang resident o documents sa database. Paki-run muna ang default seeders.');
            return;
        }

        $queueCounter = 1;

        // I-loop ang bawat dokumento
        foreach ($documents as $doc) {
            // I-loop ang bawat status
            foreach ($statuses as $status) {
                
                // Gagawa ng lima (5) para sa bawat status ng dokumentong ito
                for ($i = 1; $i <= 5; $i++) {
                    $resident = $residents->random();
                    $channel = rand(0, 1) ? 'Online' : 'Walk-in';
                    $prefix = $channel === 'Online' ? 'O-' : 'W-';
                    $queueNumber = $prefix . str_pad($queueCounter, 3, '0', STR_PAD_LEFT);

                    ServiceRequest::create([
                        'user_id' => $resident->id,
                        'document_type_id' => $doc->id,
                        'request_channel' => $channel,
                        'queue_number' => $queueNumber,
                        'purpose' => 'Test Batch Processing Data',
                        'payment_method' => 'Cash',
                        'status' => $status,
                        // I-randomize natin ang date para mas makatotohanan sa analytics
                        'created_at' => now()->subDays(rand(0, 14))->subHours(rand(1, 12)),
                        'updated_at' => now(),
                    ]);

                    $queueCounter++;
                }
            }
        }

        $this->command->info('Matagumpay na nakapag-generate ng libo-libong test requests!');
    }
}