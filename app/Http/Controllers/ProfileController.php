<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator; // 1. TINAWAG NATIN ANG BAGONG SERVICE
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    protected $emailService;

    // 2. THE LARAVEL WAY: Dependency Injection sa Constructor
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Mag-generate at magpadala ng Email OTP
     */
    public function sendEmailOtp(Request $request)
    {
        $user = Auth::user();

        // THE LARAVEL WAY (SECURITY): Rate Limiter (1 request per 60 seconds)
        $rateLimitKey = 'resend_email_otp_'.$user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return back()
                ->withErrors([
                    'email_otp' => "Masyadong mabilis! Maghintay ng {$seconds} segundo bago mag-request ulit.",
                ])
                ->with('active_tab', 'settings');
        }

        if (! $user->email) {
            return back()
                ->withErrors(['email' => 'Walang nakarehistrong email sa account na ito.'])
                ->with('active_tab', 'settings');
        }

        // Mag-generate ng 6-digit code
        $otp = (string) rand(100000, 999999);

        $user->update([
            'email_otp_code' => $otp,
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        // I-lock ang user ng 60 seconds bago makapag-send ulit
        RateLimiter::hit($rateLimitKey, 60);

        // 3. THE FIX: Pinalitan ang Log::info ng pormal na Service Call
        $this->emailService->sendEmail(
            $user->id,
            $user->email,
            'BDLS Email Verification',
            "Ang iyong BDLS Email Verification Code ay {$otp}",
        );

        return back()->with([
            'success' => 'Naipadala na ang 6-digit code sa iyong email!',
            'active_tab' => 'settings',
        ]);
    }

    /**
     * I-verify ang inilagay na Email OTP
     */
    public function verifyEmailOtp(Request $request)
    {
        // 1. MANUAL VALIDATION (Para masalo ang error at malagyan ng active_tab)
        $validator = Validator::make(
            $request->all(),
            [
                'email_otp' => 'required|size:6',
            ],
            [
                'email_otp.required' => 'Pakilagay ang 6-digit code.',
                'email_otp.size' => 'Ang code ay dapat eksaktong 6 digits.',
            ],
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('active_tab', 'settings');
        }

        $user = Auth::user();

        // 2. CHECK KUNG MALI ANG OTP
        if ($user->email_otp_code !== $request->email_otp) {
            return back()
                ->withErrors(['email_otp' => 'Mali ang 6-digit code. Subukan muli.'])
                ->with('active_tab', 'settings');
        }

        // 3. CHECK KUNG EXPIRED NA
        if (now()->greaterThan($user->email_otp_expires_at)) {
            return back()
                ->withErrors(['email_otp' => 'Expired na ang code. Mag-request ng bago.'])
                ->with('active_tab', 'settings');
        }

        // 4. SUCCESS: I-save nang direkta para iwas Fillable array errors
        $user->email_verified_at = now();
        $user->email_otp_code = null;
        $user->save();

        return back()->with([
            'success' => 'Email Verified! Maaari ka nang makatanggap ng digital receipts.',
            'active_tab' => 'settings',
        ]);
    }

    /**
     * Mag-add ng bagong email ang user mula sa Settings Tab
     */
    public function addEmail(Request $request)
    {
        $request->validate(
            [
                'new_email' => 'required|email|unique:users,email',
            ],
            [
                'new_email.required' => 'Pakilagay ang iyong email address.',
                'new_email.email' => 'Mali ang format ng email.',
                'new_email.unique' => 'Ginamit na ng ibang account ang email na ito.',
            ],
        );

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

        // 4. THE FIX: Pinalitan ang Log::info ng pormal na Service Call
        $this->emailService->sendEmail(
            $user->id,
            $user->email,
            'BDLS Email Verification',
            "Ang iyong BDLS Email Verification Code ay {$otp}",
        );

        return back()->with([
            'success' => 'Email naidagdag! Nagpadala kami ng 6-digit code upang ma-verify ito.',
            'active_tab' => 'settings',
        ]);
    }

    /**
     * I-update kung gusto ng user makatanggap ng Email Notifications (Fallback)
     */
    public function updateEmailPreference(Request $request)
    {
        $user = Auth::user();

        // THE LARAVEL WAY: I-check kung pinindot ba ang checkbox.
        // (Kung oo = 1, kung hindi = 0)
        $user->wants_email_notification = $request->has('wants_email_notification') ? 1 : 0;
        $user->save();

        // Ibalik sa Settings tab kalakip ang success message
        return back()->with([
            'success' => 'Na-update na ang iyong Notification Preferences!',
            'active_tab' => 'settings',
        ]);
    }

    /**
     * UNIVERSAL: Update Password (Admin & Resident)
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.min' => 'Ang password ay dapat hindi bababa sa 8 characters.',
            'password.confirmed' => 'Hindi tugma ang Confirm Password.'
        ]);

        $user = Auth::user();

        // I-check kung tama ang lumang password bago payagan
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mali ang iyong kasalukuyang password.'])->with('active_tab', 'settings');
        }

        // THE LARAVEL WAY: Ligtas na i-hash ang bagong password
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with([
            'success_message' => 'Matagumpay na nabago ang iyong password!',
            'active_tab' => 'settings'
        ]);
    }

    /**
     * Palitan ang Contact Number (NO LOGOUT WAY + 1-MINUTE COOLDOWN)
     */
    public function updateContactNumber(Request $request, \App\Services\SmsService $smsService)
    {
        $user = Auth::user();

        // THE FIX: 1-Minute Rate Limiter Security
        $rateLimitKey = 'update_contact_' . $user->id;
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors([
                'contact_number' => "Masyadong mabilis! Maghintay ng {$seconds} segundo bago mag-request ulit."
            ])->with('active_tab', 'settings');
        }

        $request->validate([
            'contact_number' => 'required|string|max:20|regex:/^09\d{9}$/|unique:users,contact_number,' . Auth::id()
        ]);

        if ($user->contact_number !== $request->contact_number) {
            $user->contact_number = $request->contact_number;
            $user->contact_verified_at = null; // Unverified na ulit

            $newOtp = (string) rand(100000, 999999);
            $user->otp_code = $newOtp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            // Magpadala ng bagong OTP
            $smsService->sendSms($user->id, $user->contact_number, "BDLS: Pinalitan mo ang iyong number. I-verify ito gamit ang OTP: {$newOtp}.", null, false, true);

            // THE FIX: I-lock ang button ng 60 seconds (1 minute) bago makapag-send ulit
            \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 60);

            return back()->with(['success' => 'Numero pinalitan! Pakilagay ang 6-digit OTP para ma-verify ito.', 'active_tab' => 'settings']);
        }

        return back()->with(['active_tab' => 'settings']);
    }

    /**
     * I-verify ang Contact OTP habang naka-login
     */
    public function verifyContactOtp(Request $request)
    {
        $request->validate(['otp_code' => 'required|size:6']);
        $user = Auth::user();

        if ($user->otp_code !== $request->otp_code) {
            return back()->withErrors(['otp_error' => 'Mali ang 6-digit code. Subukan muli.'])->with('active_tab', 'settings');
        }
        if (now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp_error' => 'Expired na ang code. Mag-request ng bago.'])->with('active_tab', 'settings');
        }

        $user->contact_verified_at = now();
        $user->otp_code = null;
        $user->save();

        return back()->with(['success' => 'Phone Number Verified Successfully!', 'active_tab' => 'settings']);
    }
}
