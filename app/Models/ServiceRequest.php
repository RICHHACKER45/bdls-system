<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes; // Gagamit tayo ng SoftDeletes para sa Audit Trail

    protected $fillable = [
        'user_id',
        'document_type_id',
        'request_channel',
        'queue_number',
        'purpose',
        'additional_details',
        'preferred_pickup_time',
        'status',
        'released_at',
        'released_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'preferred_pickup_time' => 'datetime',
            'released_at' => 'datetime',
        ];
    }
}
