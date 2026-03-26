<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        // Burahin ang session para malinis ang browser
        $request->session()->forget('registration_contact');
        
        // I-redirect sa Login page kasama ang Success Message
        return redirect('/login')->with('success', 'Number Verified! Hinihintay na lamang ang Admin Approval bago makapag-login.');
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
}

