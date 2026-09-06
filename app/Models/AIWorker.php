<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIWorker extends Model
{
    protected $guarded = [];

    protected $casts = [
        'capabilities' => 'array',
        'last_seen_at' => 'datetime',
    ];
}
