<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['service_request_id', 'file_path'];

    // THE FIX: Idinagdag natin ang missing relationship para hindi mag-500 Error ang Gatekeeper
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}   