<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
}