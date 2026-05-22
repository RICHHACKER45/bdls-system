<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthAndOtpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1: Hindi dapat makapasok ang user kung hindi pa verified ang number.
     */
    public function test_login_requires_verified_contact()
    {
        $user = User::factory()->create([
            'contact_number' => '09123456789',
            'password' => bcrypt('password123'),
            'contact_verified_at' => null, // Unverified Number
            'role' => 'resident'
        ]);

        $response = $this->post(route('login.post'), [
            'login_id' => '09123456789',
            'password' => 'password123'
        ]);

        // Dapat ibato siya pabalik sa /otp page
        $response->assertRedirect('/otp');
        $response->assertSessionHasErrors('otp');
        
        // Hindi dapat siya ma-login
        $this->assertGuest();
    }

    /**
     * TEST 2: Dapat harangin ng Rate Limiter ang nag-i-spam ng Resend OTP.
     */
    public function test_rate_limiter_blocks_spam_otp_requests()
    {
        $ipAddress = '127.0.0.1';
        
        // I-simulate na pinindot niya ang "Resend" kaya na-lock ng 60 seconds
        RateLimiter::hit('resend_sms_otp_' . $ipAddress, 60);

        // I-simulate na pinindot niya ulit agad
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
                         ->post(route('otp.resend'));

        // Dapat mag-error na "Masyado pang mabilis"
        $response->assertSessionHasErrors('otp_error');
    }
}