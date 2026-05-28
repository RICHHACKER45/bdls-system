<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Dapat laging true ito para makapag-register ang lahat
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|string|in:Male,Female',

            'dob_month' => 'required|numeric|min:1|max:12',
            'dob_day' => 'required|numeric|min:1|max:31',
            'dob_year' => 'required|numeric',

            // 1. PINAGSAMA NA NATIN ANG ADDRESS (Pinalitan ang house_number at purok_street)
            'address' => 'required|string|max:255',

            'contact_number' => 'required|string|max:20|unique:users,contact_number',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            // 2. NAIWAN ANG ID, PERO TULUYAN NANG BURADO ANG SELFIE DITO
            'id_photo_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            
            'terms' => 'accepted',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'terms.accepted' => 'Kailangan mong sumang-ayon sa Privacy Policy at Terms & Conditions.',
        ];
    }
}