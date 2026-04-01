<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    // THE LARAVEL WAY: Mass Assignment Protection
    protected $fillable = [
        'user_id',
        'service_request_id',
        'channel',
        'recipient_contact',
        'message_content',
        'status',
        'provider_response',
    ];
}