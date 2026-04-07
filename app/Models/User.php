<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ACCOUNT FILTERING SCOPES (The Laravel Way)
     * Upang mailipat ang filtering logic sa Database level sa halip na In-Memory Collection.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_verified', false)->where('rejection_count', '<', 5);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_verified', false)->where('rejection_count', '>=', 5);
    }

    /**
     * VIRTUAL ATTRIBUTE: Age
     * Inalis ang calculation sa Blade view para sa Better Maintainability.
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn() => \Carbon\Carbon::parse($this->date_of_birth)->age,
        );
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
        // THE LARAVEL WAY: Bagong Rejection Tracking Columns
        'rejection_reason',
        'rejection_count',
        'rejected_at',
        'locked_until',
        'is_verified',
        'terms_accepted_at', // Dinagdag para sa Audit
        'signup_ip', // Dinagdag para sa Security
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
