<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

// <-- THE LARAVEL WAY: Clear namespace imports

class NotificationLog extends Model
{
    use HasFactory, Prunable;

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

    /**
     * ELOQUENT RELATIONSHIPS
     */

    // 1. Kunin ang impormasyon ng residenteng pinadalhan ng text/email
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 2. Kunin ang impormasyon ng ni-request na dokumento (kung naka-link)
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Delete logs older than 30 days to prevent DB flooding
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }
}
