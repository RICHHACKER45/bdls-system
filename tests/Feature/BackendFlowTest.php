<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendFlowTest extends TestCase
{
    // Ginagamit ito para i-reset ang database bago at pagkatapos ng test
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mag-seed ng isang dummy document type bago mag-test
        DocumentType::create([
            'name' => 'Barangay Clearance',
            'requirements_description' => 'Valid ID',
            'is_active' => 1
        ]);
    }

    /**
     * TEST 1: Resident Online Request Creation
     */
    public function test_resident_can_create_online_request(): void
    {
        // 1. Gumawa ng pekeng resident account
        $resident = User::factory()->create([
            'role' => 'resident',
            'is_verified' => true,
            'contact_number' => '09123456789'
        ]);

        // 2. I-simulate ang pag-login at pagpasa ng request
        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'document_type_id' => 1,
            'purpose' => 'For Employment',
            'preferred_pickup_time' => now()->addDays(2)->toDateTimeString(),
        ]);

        // 3. I-check kung tama ba ang nangyari sa Database
        $this->assertDatabaseHas('service_requests', [
            'user_id' => $resident->id,
            'request_channel' => 'Online',
            'status' => 'pending',
            'purpose' => 'For Employment'
        ]);

        // 4. I-check kung nag-redirect ba nang maayos pabalik sa dashboard
        $response->assertRedirect(route('resident.dashboard'));
        $response->assertSessionHas('success_message');
    }

    /**
     * TEST 2: Admin Walk-in Processing with Shadow Profile
     */
    public function test_admin_can_create_walkin_request_with_shadow_profile(): void
    {
        // 1. Gumawa ng pekeng admin account
        $admin = User::factory()->create(['role' => 'admin']);

        // 2. I-simulate ang pag-submit ni Admin ng Walk-in form para sa bagong tao
        $response = $this->actingAs($admin)->post(route('admin.walkin.store'), [
            'contact_number' => '09987654321',
            'is_new_user' => 1,
            'first_name' => 'Walkin',
            'last_name' => 'User',
            'sex' => 'Male',
            'date_of_birth' => '1995-01-01',
            'house_number' => '123',
            'purok_street' => 'Purok 1',
            'document_type_id' => 1,
            'purpose' => 'Walk-in Purpose',
        ]);

        // 3. I-check kung nagawa ba ang SHADOW PROFILE sa users table
        $this->assertDatabaseHas('users', [
            'contact_number' => '09987654321',
            'first_name' => 'Walkin',
            'is_verified' => 1 // Automatic verified dahil sa Admin dumaan
        ]);

        // 4. I-check kung nagawa ba ang REQUEST
        $this->assertDatabaseHas('service_requests', [
            'request_channel' => 'Walk-in',
            'purpose' => 'Walk-in Purpose',
        ]);
        
        // 5. I-check kung nai-record sa Audit Logs ang galaw ni Admin
        $this->assertDatabaseHas('audit_logs', [
            'admin_id' => $admin->id,
            'action' => 'WALKIN_ENCODED',
        ]);
    }

    /**
     * TEST 3: Admin Status Update & Soft Delete Logic
     */
    public function test_admin_can_update_status_and_soft_delete_rejected_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = User::factory()->create(['role' => 'resident']);

        // Gumawa ng pending request
        $request = ServiceRequest::create([
            'user_id' => $resident->id,
            'document_type_id' => 1,
            'request_channel' => 'Online',
            'queue_number' => 'O-001',
            'purpose' => 'Testing',
            'preferred_pickup_time' => now(),
            'status' => 'pending',
        ]);

        // I-simulate na ni-reject ito ni Admin
        $response = $this->actingAs($admin)->post(route('admin.request.update_status', $request->id), [
            'status' => 'rejected'
        ]);

        // Kung rejected, dapat nag-Soft Delete ito sa database (Deleted_at is not null)
        $this->assertSoftDeleted('service_requests', [
            'id' => $request->id,
            'status' => 'rejected'
        ]);
    }
}