<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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

        // STEP 4: I-save nang tuluyan sa Database (users table)
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
            'password' => Hash::make($validatedData['password']), // Ligtas na naka-hash
            'id_photo_path' => $idPhotoPath,
            'selfie_photo_path' => $selfiePath,
            'role' => 'resident',
            'is_verified' => false,
        ]);

        // STEP 5: Pambihirang testing output para makita mo agad kung gumana!
        dd('SUCCESS, LEAD DEV! Nasa Database na ang user!', $user);
    }
}