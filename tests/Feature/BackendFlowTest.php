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

    protected $docType; // <-- THE FIX: Dynamic ID Holder

    protected function setUp(): void
    {
        parent::setUp();

        // THE FIX: I-save sa property para laging tama ang ID kahit mag-increment
        $this->docType = DocumentType::create([
            'name' => 'Barangay Clearance',
            'requirements_description' => 'Valid ID',
            'is_active' => 1,
        ]);
    }

    public function test_resident_can_create_online_request(): void
    {
        $resident = User::factory()->create([
            'role' => 'resident',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'document_type_id' => $this->docType->id,
            'purpose' => 'For Employment',
            'preferred_pickup_time' => now()->addDays(2)->toDateTimeString(),
            // THE FIX: Idinagdag natin ito para hindi mag "Undefined Array Key" ang Controller
            'additional_details' => null,
        ]);

        // THE FIX: I-check natin kung nag-Success Redirect (302) para harangin ang 500 Server Error WSoD
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_requests', [
            'user_id' => $resident->id,
            'request_channel' => 'Online',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_create_walkin_request_with_shadow_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.walkin.store'), [
            'contact_number' => '09987654321',
            'is_new_user' => 1,
            'first_name' => 'Walkin',
            'last_name' => 'User',
            'sex' => 'Male',
            'date_of_birth' => '1995-01-01',
            'house_number' => '123',
            'purok_street' => 'Purok 1',
            'document_type_id' => $this->docType->id, // <-- DYNAMIC ID
            'purpose' => 'Walk-in Purpose',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'contact_number' => '09987654321',
            'first_name' => 'Walkin',
        ]);
    }

    public function test_admin_can_update_status_and_soft_delete_rejected_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = User::factory()->create(['role' => 'resident']);

        $request = ServiceRequest::create([
            'user_id' => $resident->id,
            'document_type_id' => $this->docType->id, // <-- DYNAMIC ID
            'request_channel' => 'Online',
            'queue_number' => 'O-001',
            'purpose' => 'Testing',
            'preferred_pickup_time' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.request.update_status', $request->id), [
            'status' => 'rejected',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertSoftDeleted('service_requests', [
            'id' => $request->id,
        ]);
    }
}
