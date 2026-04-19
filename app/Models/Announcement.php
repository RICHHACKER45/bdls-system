<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    // Idagdag ito para payagan ang mass assignment
    protected $fillable = ['admin_id', 'message_body'];
}
