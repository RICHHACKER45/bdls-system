<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // BINURA: Tinanggal ko ang scopePending, scopeApproved, at scopeRejected
    // Dahilan: Hindi na tayo gagamit ng manual rejection at approval ni Admin. Automated na tayo via Census.

    /**
     * VIRTUAL ATTRIBUTE: Age
     */
    protected function age(): Attribute
    {
        return Attribute::make(get: fn () => Carbon::parse($this->date_of_birth)->age);
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'date_of_birth',
        'address',
        'contact_number',
        'email',
        'password',

        // 🛑 ZERO-RETENTION: Binura na natin ang 'id_photo_path' dito.

        'role',
        'otp_code',
        'otp_expires_at',
        'contact_verified_at',
        'email_verified_at',
        'email_otp_code',
        'email_otp_expires_at',
        'wants_email_notification',

        // ==========================================
        // 🛡️ THE LARAVEL WAY: NEW SECURITY POLICIES
        // ==========================================
        'ocr_attempts',
        'ocr_locked_until',
        'otp_request_count',
        'otp_request_locked_until',
        'otp_failed_attempts',
        'locked_until',
        'is_active',
        'force_password_change',

        'is_verified',
        'terms_accepted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = ['password', 'otp_code', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'contact_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
            'wants_email_notification' => 'boolean',
            'is_verified' => 'boolean',

            // 🛡️ BAGONG DATES AT BOOLEANS PARA SA SECURITY POLICIES:
            'ocr_locked_until' => 'datetime',
            'otp_request_locked_until' => 'datetime',
            'locked_until' => 'datetime', // NAIWAN para sa 10-failed OTPs
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
        ];
    }
}