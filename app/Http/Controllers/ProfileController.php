<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Mag-generate at magpadala ng Dummy Email OTP
     */
    public function sendEmailOtp(Request $request)
    {

        $user = Auth::user();

        // THE LARAVEL WAY (SECURITY): Rate Limiter (1 request per 60 seconds)
        $rateLimitKey = 'resend_email_otp_' . $user->id;
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors(['email_otp' => "Masyadong mabilis! Maghintay ng {$seconds} segundo bago mag-request ulit."])->with('active_tab', 'settings');
        }

        if (!$user->email) {
            return back()->withErrors(['email' => 'Walang nakarehistrong email sa account na ito.'])->with('active_tab', 'settings');
        }

        // Mag-generate ng 6-digit code
        $otp = (string) rand(100000, 999999);
        
        $user->update([
            'email_otp_code' => $otp,
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        // I-lock ang user ng 60 seconds bago makapag-send ulit
        RateLimiter::hit($rateLimitKey, 60);

        // DUMMY EMAIL INTEGRATION
        Log::info("DUMMY EMAIL SENT to {$user->email}: Ang iyong BDLS Email Verification Code ay {$otp}");

        return back()->with(['success' => 'Naipadala na ang 6-digit code sa iyong email!', 'active_tab' => 'settings']);
    }

    /**
     * I-verify ang inilagay na Email OTP
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email_otp' => 'required|size:6'
        ], [
            'email_otp.required' => 'Pakilagay ang 6-digit code.',
            'email_otp.size' => 'Ang code ay dapat eksaktong 6 digits.'
        ]);

        $user = Auth::user();

        if ($user->email_otp_code !== $request->email_otp) {
            return back()->withErrors(['email_otp' => 'Mali ang 6-digit code. Subukan muli.'])->with('active_tab', 'settings');
        }

        if (now()->greaterThan($user->email_otp_expires_at)) {
            return back()->withErrors(['email_otp' => 'Expired na ang code. Mag-request ng bago.'])->with('active_tab', 'settings');
        }

        // SUCCESS: I-update ang email_verified_at
        $user->update([
            'email_verified_at' => now(),
            'email_otp_code' => null, // Burahin ang code para sa security
        ]);

        return back()->with(['success' => 'Email Verified! Maaari ka nang makatanggap ng digital receipts.', 'active_tab' => 'settings']);
    }

     /**
     * Mag-add ng bagong email ang user mula sa Settings Tab
     */
    public function addEmail(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email'
        ], [
            'new_email.required' => 'Pakilagay ang iyong email address.',
            'new_email.email' => 'Mali ang format ng email.',
            'new_email.unique' => 'Ginamit na ng ibang account ang email na ito.'
        ]);

        $user = Auth::user();

        // I-save ang email pero Unverified pa
        $user->update([
            'email' => $request->new_email,
            'email_verified_at' => null, 
        ]);

        // Mag-generate at mag-send agad ng OTP
        $otp = (string) rand(100000, 999999);
        $user->update([
            'email_otp_code' => $otp,
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        // DUMMY EMAIL INTEGRATION
        Log::info("DUMMY EMAIL SENT to {$user->email}: Ang iyong BDLS Email Verification Code ay {$otp}");

        return back()->with(['success' => 'Email naidagdag! Nagpadala kami ng 6-digit code upang ma-verify ito.', 'active_tab' => 'settings']);
    }
}
