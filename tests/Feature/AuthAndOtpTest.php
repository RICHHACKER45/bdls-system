<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthAndOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_verified_contact()
    {
        // THE FIX: Tinanggal ang double-hashed bcrypt.
        // Hayaan na ang UserFactory ang mag-assign ng 'password123'
        $user = User::factory()->create([
            'contact_number' => '09123456789',
            'contact_verified_at' => null, // Unverified Number
            'role' => 'resident'
        ]);

        $response = $this->post(route('login.post'), [
            'login_id' => '09123456789',
            'password' => 'password123' // Tumugma sa Factory Default
        ]);

        $response->assertRedirect('/otp');
        $response->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    public function test_rate_limiter_blocks_spam_otp_requests()
    {
        $ipAddress = '127.0.0.1';
        RateLimiter::hit('resend_sms_otp_' . $ipAddress, 60);

        $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
                         ->post(route('otp.resend'));

        $response->assertSessionHasErrors('otp_error');
    }
}