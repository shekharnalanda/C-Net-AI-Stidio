<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIJob extends Model
{
    protected $table = 'ai_jobs';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
