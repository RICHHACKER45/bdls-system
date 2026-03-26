<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Ito yung mga fields na pinapayagan nating i-fill up ng user sa signup form.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'house_number',
        'purok_street',
        'contact_number',
        'email',
        'password',
        'id_photo_path',
        'selfie_photo_path',
        'role',
        'otp_code',
        'otp_expires_at',
        'contact_verified_at',
        'is_verified',
        // Dito natin idinagdag ang tatlong bago:
        'email_verified_at',
        'email_otp_code',
        'email_otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Ito yung mga sensitibong data na hindi dapat lumabas kapag nag-API response tayo.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'otp_code', // Tinago natin ang OTP para secure!
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * PHP 8+ / Laravel 11 Casts Method - kino-convert niya ang data sa tamang format.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'contact_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean', // Kino-convert ang tinyint(1) [4] sa true/false
        ];
    }
}