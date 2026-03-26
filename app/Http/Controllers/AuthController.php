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

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // STEP 1: I-validate ang lahat ng pumapasok na data mula sa signup form
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:5',
            
            'dob_month' => 'required|numeric|min:1|max:12',
            'dob_day' => 'required|numeric|min:1|max:31',
            'dob_year' => 'required|numeric',
            
            'house_number' => 'required|string|max:255',
            'purok_street' => 'required|string|max:255',
            
            'contact_number' => 'required|string|max:20|unique:users,contact_number',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            
            'id_photo_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'selfie_photo_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'privacy' => 'required|accepted'
        ]);

        // STEP 2: I-format ang Date of Birth (YYYY-MM-DD para sa SQL)
        // Gumagamit tayo ng str_pad para maging "01" kapag "1" lang ang buwan/araw
        $dateOfBirth = $validatedData['dob_year'] . '-' . 
                       str_pad($validatedData['dob_month'], 2, '0', STR_PAD_LEFT) . '-' . 
                       str_pad($validatedData['dob_day'], 2, '0', STR_PAD_LEFT);

        // STEP 3: I-save ang mga Images sa Server Storage (public disk)
        $idPhotoPath = $request->file('id_photo_path')->store('verification_ids', 'public');
        $selfiePath = $request->file('selfie_photo_path')->store('verification_selfies', 'public');

        // STEP 4 & 5: Gagamit tayo ng Database Transaction para safe!
        // Kung may mag-error sa loob nito (tulad ng sirang SMS API), hindi mase-save ang User sa database.
        DB::transaction(function () use ($validatedData, $dateOfBirth, $idPhotoPath, $selfiePath, $request) {
            
            $otpCode = (string) rand(100000, 999999);
            $otpExpiresAt = now()->addMinutes(10);

            // I-save sa database (Naka-pending pa ito sa transaction)
            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'middle_name' => $validatedData['middle_name'],
                'last_name' => $validatedData['last_name'],
                'suffix' => $validatedData['suffix'],
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
            ]);

            // DUMMY SMS INTEGRATION
            // Kung mag-error ito, automatic mabu-bura si User sa itaas!
            Log::info("DUMMY SMS SENT to {$user->contact_number}: Ang iyong BDLS OTP ay {$otpCode}");

            $request->session()->put('registration_contact', $user->contact_number);
        });

        // Kapag lumabas na dito ang code, ibig sabihin 100% SUCCESS ang transaction!
        return redirect('/otp')->with('success', 'Registration successful! Nagpadala kami ng code sa iyong numero.');

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
            return redirect('/signup')->withErrors(['error' => 'Session expired. Mangyaring mag-register muli.']);
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

        // SUCCESS LENS: I-update ang database na "Verified Number" na siya!
        $user->update([
            'contact_verified_at' => now(),
            'otp_code' => null, // Burahin ang ginamit na code para sa security
        ]);

        // Burahin ang OTP session memory
        $request->session()->forget('registration_contact');

        // THE LARAVEL WAY: I-login agad ang user dahil napatunayan na niya ang OTP niya
        Auth::login($user);

        // SECURITY: Regenerate session laban sa session fixation attacks
        $request->session()->regenerate();

        // I-redirect diretso sa Resident Dashboard
        return redirect('/resident/dashboard')->with('success', 'Number Verified! Welcome sa iyong dashboard.');
    }

    /**
     * Mag-generate ng bagong OTP at i-update ang expiration
     */
    public function resendOtp(Request $request)
    {
        $contactNumber = $request->session()->get('registration_contact');

        if (!$contactNumber) {
            return redirect('/signup')->withErrors(['error' => 'Session expired. Mangyaring mag-register muli.']);
        }

        $user = User::where('contact_number', $contactNumber)->first();

        // Mag-generate ng BAGONG 6-digit OTP
        $newOtpCode = (string) rand(100000, 999999);
        
        $user->update([
            'otp_code' => $newOtpCode,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // DUMMY SMS INTEGRATION PARA SA RESEND
        Log::info("DUMMY SMS RESENT to {$user->contact_number}: Ang iyong BAGONG BDLS OTP ay {$newOtpCode}");

        return back()->with('success', 'Ang bagong 6-digit code ay naipadala na sa iyong numero!');
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
        $loginType = filter_var($credentials['login_id'], FILTER_VALIDATE_EMAIL) ? 'email' : 'contact_number';

        // 3. Subukang i-authenticate (The Laravel Way)
        if (Auth::attempt([$loginType => $credentials['login_id'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            // 3. Subukang i-authenticate (The Laravel Way)
        if (Auth::attempt([$loginType => $credentials['login_id'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            // 4. ANG OTP SHIELD: I-check kung tapos na siya sa OTP Verification (Kung null ang timestamp)
            if (is_null($user->contact_verified_at)) {
                Auth::logout(); // I-kick palabas sa system
                
                // I-restore ang session memory para gumana ulit ang OTP page niya
                $request->session()->put('registration_contact', $user->contact_number);
                
                // Ibato pabalik sa OTP page kalakip ang error message
                return redirect('/otp')->withErrors(['otp' => 'Hindi pa verified ang iyong numero. Pakilagay ang OTP code upang makapagpatuloy.']);
            }

            // (Nakatago pa rin ang Admin Shield natin para makapag-design tayo)
            // if (!$user->is_verified) { ... }

            // 5. SECURITY: Regenerate session laban sa hackers
            $request->session()->regenerate();

            // 6. I-redirect sa Resident Dashboard
            return redirect()->intended('/resident/dashboard');
        }

            // 5. SECURITY: Regenerate session laban sa hackers
            $request->session()->regenerate();

            // 6. I-redirect sa Resident Dashboard (Gagawa tayo nito mamaya)
            return redirect()->intended('/resident/dashboard');
        }

        // Kapag mali ang password o number/email
        return back()->withErrors(['login_id' => 'Mali ang Contact Number/Email o Password na inilagay.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // CSRF Protection
        return redirect('/login');
    }

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

