<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class AuditLog extends Model
{
    use Prunable;

    // 1. THE LARAVEL WAY: I-disable ang updated_at dahil bawal i-edit ang log
    const UPDATED_AT = null;

    // 2. SECURITY: Mass Assignment Protection
    protected $fillable = ['admin_id', 'action', 'description'];

    // 3. ELOQUENT RELATIONSHIP: Kunin ang data ng Admin na gumawa ng action
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Delete logs older than 30 days to prevent DB flooding
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }
}
