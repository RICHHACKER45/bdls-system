<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
   // THE FIX: Payagan ang system na i-save ang mga data na ito (Mass Assignment)
    protected $fillable = [
        'name', 
        'requirements_description', 
        'is_active'
    ];  
}
