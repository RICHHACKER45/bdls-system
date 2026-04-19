<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // 1. THE LARAVEL WAY: I-disable ang updated_at dahil bawal i-edit ang log
    const UPDATED_AT = null;

    // 2. SECURITY: Mass Assignment Protection
    protected $fillable = [
        'admin_id',
        'action',
        'description',
    ];

    // 3. ELOQUENT RELATIONSHIP: Kunin ang data ng Admin na gumawa ng action
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}