<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * TASK 1: Fixed Scopes for zero-tolerance pending and multi-attempt rejection
     */

    /**
     * ACCOUNT FILTERING SCOPES (The Laravel Way)
     */

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
        'id_photo_path',
        'role',
        'otp_code',
        'otp_expires_at',
        'contact_verified_at',
        'email_verified_at',
        'email_otp_code',
        'email_otp_expires_at',
        'wants_email_notification',
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
        ];
    }
}
