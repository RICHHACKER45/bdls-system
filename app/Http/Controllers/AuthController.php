<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\SmsService;

use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function register(RegisterRequest $request)
    {
        // STEP 1: Get validated data
        $validatedData = $request->validated();

        // STEP 2: I-format ang Date of Birth (YYYY-MM-DD para sa SQL)
        $dateOfBirth =
            $validatedData['dob_year'] .
            '-' .
            str_pad($validatedData['dob_month'], 2, '0', STR_PAD_LEFT) .
            '-' .
            str_pad($validatedData['dob_day'], 2, '0', STR_PAD_LEFT);

        // STEP 3: I-save ang mga Images sa Server Storage (public disk)
        $idPhotoPath = $request->file('id_photo_path')->store('verification_ids', 'public');
        $selfiePath = $request->file('selfie_photo_path')->store('verification_selfies', 'public');

        // STEP 4 & 5: Database Transaction
        DB::transaction(function () use (
            $validatedData,
            $dateOfBirth,
            $idPhotoPath,
            $selfiePath,
            $request,
        ) {
            $otpCode = (string) rand(100000, 999999);
            $otpExpiresAt = now()->addMinutes(10);

            // I-save sa database (Naka-pending pa ito sa transaction)
            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'middle_name' => $validatedData['middle_name'],
                'last_name' => $validatedData['last_name'],
                'suffix' => $validatedData['suffix'] ?? null,
                'sex' => $validatedData['sex'],
                'date_of_birth' => $dateOfBirth,
                'house_number' => $validatedData['house_number'],
                'purok_street' => $validatedData['purok_street'],
                'contact_number' => $validatedData['contact_number'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'id_photo_path' => $idPhotoPath,
                'selfie_photo_path' => $selfiePath,
                'role' => 'resident',
                'is_verified' => false,
                'otp_code' => $otpCode,
                'otp_expires_at' => $otpExpiresAt,
                // AUTOMATIC AUDIT TRAIL (Hindi na ito ita-type ng user)
                'terms_accepted_at' => now(), // Kukunin ng Laravel ang petsa at oras ngayon
            ]);

            // TOTOONG SMS INTEGRATION: OTP Generation (Ligtas: 1 Credit)
            $message = "Ang iyong OTP code ay {$otpCode}. Ito ay mag-e-expire sa loob ng 10 minuto.";
            $this->smsService->sendSms(
                $user->id,
                $user->contact_number,
                $message,
                null,
                false,
                true,
            );

            $request->session()->put('registration_contact', $user->contact_number);
        });

        // Kapag lumabas na dito ang code, ibig sabihin 100% SUCCESS ang transaction!
        return redirect('/otp')->with(
            'success',
            'Registration successful! Nagpadala kami ng code sa iyong numero.',
        );
    }

    /**
     * Sasalo at magbe-verify sa 6-digit OTP code mula sa otp.blade.php
     */
    public function verifyOtp(Request $request)
    {
        // I-combine ang 6 na kahon mula sa Front-End
        $otpArray = $request->input('otp');
        $enteredOtp = implode('', $otpArray);

        // Hanapin kung kaninong session ito naka-link
        $contactNumber = $request->session()->get('registration_contact');
        if (!$contactNumber) {
            return redirect('/signup')->withErrors([
                'error' => 'Session expired. Mangyaring mag-register muli.',
            ]);
        }

        $user = User::where('contact_number', $contactNumber)->first();

        // I-check kung tama ang OTP
        if ($user->otp_code !== $enteredOtp) {
            return back()->withErrors(['otp' => 'Mali ang 6-digit code. Subukan muli.']);
        }

        // I-check kung Expired na (10 minutes rule)
        if (now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Expired na ang OTP code. Mag-request ng bago.']);
        }

        // SUCCESS LENS: I-wrap sa Transaction!
        DB::transaction(function () use ($user) {
            // 1. I-update ang database na "Verified Number" na siya
            $user->update([
                'contact_verified_at' => now(),
                'otp_code' => null, // Burahin ang ginamit na code para sa security
            ]);

            // ==========================================
            // 2. SMS: ACCOUNT UNDER REVIEW (Workflow Step 5)
            // ==========================================
            if ($user->role === 'resident') {
                $message =
                    'Number verified! Ang account mo ay sinusuri pa ng Admin. Maghintay ng text confirmation bago mag-request ng dokumento.';

                $this->smsService->sendSms($user->id, $user->contact_number, $message, null);
            }
        });

        // THE LARAVEL WAY: I-login at i-regenerate ang session SA LABAS ng transaction
        // Gagawin lang ito ng system KUNG SUCCESSFUL ang database update at SMS sa itaas.
        $request->session()->forget('registration_contact');
        Auth::login($user);
        $request->session()->regenerate();

        // ==========================================
        // ROLE-BASED ROUTING
        // ==========================================
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard')->with(
                'success',
                'Admin Account Verified! Welcome.',
            );
        }

        return redirect('/resident/dashboard')->with(
            'success',
            'Number Verified! Welcome sa iyong dashboard.',
        );
    }

    public function resendOtp(Request $request)
    {
        // 1. I-setup ang dalawang susi (keys) gamit ang IP address ng user
        $cooldownKey = 'resend_sms_otp_' . $request->ip(); // Para sa 60s timer
        $blockKey = 'block_sms_otp_' . $request->ip(); // Para sa 3-strike block

        // 2. CHECK: Naka-3 beses na ba siya? (Naka-lock ng 1 oras kapag spammer)
        if (RateLimiter::tooManyAttempts($blockKey, 3)) {
            return back()->withErrors([
                'otp_error' =>
                    'Na-block ang iyong IP dahil sa maraming attempts. Subukan ulit mamaya.',
            ]);
        }

        // 3. CHECK: Nakapag-hintay na ba siya ng 60 seconds?
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            return back()->withErrors([
                'otp_error' => 'Masyado pang mabilis. Maghintay bago mag-resend.',
            ]);
        }

        /* 
        ====================================================
        DITO MO ILALAGAY YUNG LOGIC MO SA PAG-SEND NG SMS
        (e.g., pag-generate ng bagong code, pag-save sa DB)
        ====================================================
        */
        // 1. Kuhanin ang number ng user na nagre-request mula sa Session memory
        $contactNumber = $request->session()->get('registration_contact');

        if (!$contactNumber) {
            return back()->withErrors([
                'otp_error' => 'Session expired. Hindi mahanap ang iyong numero.',
            ]);
        }

        $user = User::where('contact_number', $contactNumber)->first();

        // 2. GUMAWA NG BAGONG OTP (Action)
        $newOtp = (string) rand(100000, 999999);

        // THE LARAVEL WAY: I-wrap sa Transaction!
        // Kapag nag-throw ng Exception ang SmsService (e.g. NTC Curfew blocker),
        // HINDI mase-save ang bagong OTP sa database. Ligtas ang lumang OTP ng user!
        DB::transaction(function () use ($user, $newOtp) {
            // 3. I-UPDATE ANG KASALUKUYANG USER
            $user->otp_code = $newOtp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            // TOTOONG SMS INTEGRATION: Resend (Ligtas: 1 Credit)
            $message = "Ang iyong BAGONG OTP code ay {$newOtp}. Ito ay mag-e-expire sa loob ng 10 minuto.";
            $this->smsService->sendSms(
                $user->id,
                $user->contact_number,
                $message,
                null,
                false,
                true,
            );
        });

        // 4. LOCK THE SYSTEM: Pagkatapos ma-send, i-lock natin sila!
        RateLimiter::hit($cooldownKey, 60); // I-lock ang button ng 60 seconds
        RateLimiter::hit($blockKey, 3600); // Dagdagan ng 1 strike ang IP block (babalik sa zero after 1 hour)

        return back()->with('success', 'Bagong OTP code ay naipadala na!');
    }

    /**
     * Authenticate ang user papasok sa system
     */
    public function login(Request $request)
    {
        // 1. I-validate ang input mula sa form
        $credentials = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Tukuyin kung Email ba o Contact Number ang tina-type ni user
        $loginType = filter_var($credentials['login_id'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'contact_number';

        // 3. Subukang i-authenticate (The Laravel Way)
        if (
            Auth::attempt([
                $loginType => $credentials['login_id'],
                'password' => $credentials['password'],
            ])
        ) {
            $user = Auth::user();

            // 4. ANG OTP SHIELD: I-check kung tapos na siya sa OTP Verification
            if (is_null($user->contact_verified_at)) {
                Auth::logout(); // I-kick palabas sa system
                // I-restore ang session memory para gumana ulit ang OTP page niya
                $request->session()->put('registration_contact', $user->contact_number);
                return redirect('/otp')->withErrors([
                    'otp' =>
                        'Hindi pa verified ang iyong numero. Pakilagay ang OTP code upang makapagpatuloy.',
                ]);
            }

            // 5. SECURITY: Regenerate session laban sa hackers
            $request->session()->regenerate();

            // ==========================================
            // 6. ROLE-BASED ROUTING (Ang Traffic Enforcer)
            // ==========================================
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }

            // Kung hindi siya admin, ibig sabihin resident siya:
            return redirect()->intended('/resident/dashboard');
        }

        // Kapag mali ang password o number/email
        return back()->withErrors([
            'login_id' => 'Mali ang Contact Number/Email o Password na inilagay.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // CSRF Protection
        return redirect('/login');
    }
}
