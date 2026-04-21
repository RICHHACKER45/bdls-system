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
    public function scopePending(Builder $query): Builder
    {
        // THE FIX: Dapat 0 ang rejection count para maituring na "Under Review" lang.
        return $query
            ->where('is_verified', false)
            ->where('rejection_count', 0)
            ->whereNotNull('contact_verified_at');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('is_verified', false)->where('rejection_count', '>', 0);
    }

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
        'email_verified_at',
        'email_otp_code',
        'email_otp_expires_at',
        'wants_email_notification',
        'rejection_reason',
        'rejection_count',
        'rejected_at',
        'locked_until',
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
            'rejected_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }
}
