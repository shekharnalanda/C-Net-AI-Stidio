<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioProject extends Model
{
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'timeline' => 'array',
    ];
}
