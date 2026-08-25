<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageContent extends Model
{
    protected $fillable = ['section_name', 'content_data'];

    protected function casts(): array
    {
        return [
            'content_data' => 'array', // Automatically serialize JSON to array
        ];
    }
}
