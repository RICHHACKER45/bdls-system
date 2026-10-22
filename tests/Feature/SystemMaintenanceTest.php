<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_resident_cannot_access_maintenance_endpoints(): void
    {
        $response = $this->get(route('admin.maintenance.logs'));
        $response->assertRedirect(route('login'));

        $resident = User::factory()->create([
            'role' => 'resident',
            'contact_verified_at' => now(),
        ]);

        $residentResponse = $this->actingAs($resident)->get(route('admin.maintenance.logs'));
        $residentResponse->assertStatus(403);
    }

    public function test_admin_can_retrieve_live_diagnostics_and_logs(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'contact_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.maintenance.logs'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'diagnostics' => [
                'database' => ['status', 'latency_ms'],
                'sms' => ['primary_gateway', 'curfew_active', 'curfew_window', 'link_blocker_enforced'],
                'vision_ocr' => ['status', 'zero_retention_policy'],
                'websockets' => ['broadcaster', 'port'],
            ],
            'logs',
        ]);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_admin_can_execute_dry_run_sms_diagnostic(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'contact_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.maintenance.test_sms'), [
            'recipient' => '09123456789',
            'situation_key' => 'new_request',
            'message' => 'Brgy Dona Lucia: Test diagnostic notification for evaluation.',
            'mode' => 'dry_run',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'mode' => 'dry_run',
        ]);
    }

    public function test_id_verification_requires_valid_image(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'contact_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.maintenance.verify_id'), []);

        $response->assertStatus(422);
    }
}
