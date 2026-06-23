<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

            // PINALITAN: Pinagsama na natin ang address
            'address' => 'required|string|max:255',

            'contact_number' => 'required|string|max:20|unique:users,contact_number',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers(),
            ],

            'id_photo_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            // BINURA: 'selfie_photo_path'

            'ocr_attempt' => 'required|integer|min:1|max:5', // 🛡️ FRONTEND TRACKER

            'terms' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'Kailangan mong sumang-ayon sa Privacy Policy at Terms & Conditions.',
        ];
    }
}
