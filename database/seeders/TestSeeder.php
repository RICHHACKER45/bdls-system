<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // ==========================================
        // 1. CREATE RESIDENTS (Approved, Pending, Rejected, Suspended)
        // ==========================================

        // RESIDENT 1: Juan (Approved & Active)
        $juan = User::create([
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'sex' => 'Male',
            'date_of_birth' => '1990-05-15', 'house_number' => '123', 'purok_street' => 'Purok 1',
            'contact_number' => '09111111111', 'email' => 'juan@test.com', 'password' => $defaultPassword,
            'id_photo_path' => 'verification_ids/dummy_id.jpg', 'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident', 'contact_verified_at' => now(), 'is_verified' => 1, 'terms_accepted_at' => now(),
        ]);

        // RESIDENT 2: Pedro (Approved & Walk-in User)
        $pedro = User::create([
            'first_name' => 'Pedro', 'last_name' => 'Penduko', 'sex' => 'Male',
            'date_of_birth' => '1985-10-20', 'house_number' => '456', 'purok_street' => 'Purok 2',
            'contact_number' => '09222222222', 'email' => 'pedro@test.com', 'password' => $defaultPassword,
            'id_photo_path' => 'verification_ids/dummy_id.jpg', 'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident', 'contact_verified_at' => now(), 'is_verified' => 1, 'terms_accepted_at' => now(),
        ]);

        // RESIDENT 3: Maria (Pending - Under Review)
        User::create([
            'first_name' => 'Maria', 'last_name' => 'Clara', 'sex' => 'Female',
            'date_of_birth' => '1998-08-20', 'house_number' => '789', 'purok_street' => 'Purok 3',
            'contact_number' => '09333333333', 'email' => 'maria@test.com', 'password' => $defaultPassword,
            'id_photo_path' => 'verification_ids/dummy_id.jpg', 'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident', 'contact_verified_at' => now(), 'is_verified' => 0, 'terms_accepted_at' => now(),
        ]);

        // RESIDENT 4: Crispin (Rejected Account - May Rason)
        User::create([
            'first_name' => 'Crispin', 'last_name' => 'Basilio', 'sex' => 'Male',
            'date_of_birth' => '2000-12-25', 'house_number' => '101', 'purok_street' => 'Purok 4',
            'contact_number' => '09444444444', 'password' => $defaultPassword,
            'id_photo_path' => 'verification_ids/dummy_id.jpg', 'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident', 'contact_verified_at' => now(), 'is_verified' => 0, 
            'rejection_count' => 3, 'rejection_reason' => 'Malabo ang Valid ID. Hindi mabasa.', 'rejected_at' => now(), 'terms_accepted_at' => now(),
        ]);

        // RESIDENT 5: Lolo Tasyo (Suspended Account - No Show Penalty)
        User::create([
            'first_name' => 'Tasyo', 'last_name' => 'Pilosopo', 'sex' => 'Male',
            'date_of_birth' => '1950-02-14', 'house_number' => '202', 'purok_street' => 'Purok 5',
            'contact_number' => '09555555555', 'password' => $defaultPassword,
            'id_photo_path' => 'verification_ids/dummy_id.jpg', 'selfie_photo_path' => 'verification_selfies/dummy_selfie.jpg',
            'role' => 'resident', 'contact_verified_at' => now(), 'is_verified' => 1, 
            'locked_until' => now()->addDays(7), 'terms_accepted_at' => now(),
        ]);

        // ==========================================
        // 2. CREATE SERVICE REQUESTS (All Statuses)
        // ==========================================

        $requests = [
            // ACTIVE QUEUE: ONLINE
            ['user_id' => $juan->id, 'doc_id' => 1, 'channel' => 'Online', 'queue' => 'O-001', 'status' => 'pending', 'purpose' => 'Trabaho'],
            ['user_id' => $juan->id, 'doc_id' => 2, 'channel' => 'Online', 'queue' => 'O-002', 'status' => 'processing', 'purpose' => 'Eskwela'],
            ['user_id' => $juan->id, 'doc_id' => 8, 'channel' => 'Online', 'queue' => 'O-003', 'status' => 'for_interview', 'purpose' => 'First Time Job Seeker'],
            ['user_id' => $juan->id, 'doc_id' => 5, 'channel' => 'Online', 'queue' => 'O-004', 'status' => 'released', 'purpose' => 'Financial Assistance'],

            // ACTIVE QUEUE: WALK-IN
            ['user_id' => $pedro->id, 'doc_id' => 3, 'channel' => 'Walk-in', 'queue' => 'W-001', 'status' => 'pending', 'purpose' => 'Senior Citizen ID'],
            ['user_id' => $pedro->id, 'doc_id' => 6, 'channel' => 'Walk-in', 'queue' => 'W-002', 'status' => 'processing', 'purpose' => 'Pangkabuhayan'],

            // RECEIVED HISTORY (Completed)
            ['user_id' => $juan->id, 'doc_id' => 4, 'channel' => 'Online', 'queue' => 'O-005', 'status' => 'received', 'purpose' => 'Solo Parent', 'released_at' => now()->subDays(2)],
            ['user_id' => $pedro->id, 'doc_id' => 1, 'channel' => 'Walk-in', 'queue' => 'W-003', 'status' => 'received', 'purpose' => 'Valid ID Requirements', 'released_at' => now()->subDay()],

            // CANCELED / REJECTED
            ['user_id' => $juan->id, 'doc_id' => 12, 'channel' => 'Online', 'queue' => 'O-006', 'status' => 'canceled', 'purpose' => 'Wrong Document'],
            ['user_id' => $pedro->id, 'doc_id' => 10, 'channel' => 'Walk-in', 'queue' => 'W-004', 'status' => 'rejected', 'purpose' => 'Kulang sa Pasa'],
        ];

        foreach ($requests as $req) {
            ServiceRequest::create([
                'user_id' => $req['user_id'],
                'document_type_id' => $req['doc_id'],
                'request_channel' => $req['channel'],
                'queue_number' => $req['queue'],
                'purpose' => $req['purpose'],
                'preferred_pickup_time' => now()->addDays(2),
                'status' => $req['status'],
                'released_at' => $req['released_at'] ?? null,
                // Soft delete if canceled/rejected per your system design
                'deleted_at' => in_array($req['status'], ['canceled', 'rejected']) ? now() : null, 
            ]);
        }
    }
}