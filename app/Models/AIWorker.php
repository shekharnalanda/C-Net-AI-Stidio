<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIWorker extends Model
{
    protected $table = 'ai_workers';

    protected $guarded = [];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'last_seen_at' => 'datetime',
        'registered_at' => 'datetime',
        'credential_rotated_at' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    public function currentJob()
    {
        return $this->belongsTo(AIJob::class, 'current_job_id');
    }

    public function isOnline(): bool
    {
        return $this->is_enabled
            && $this->last_seen_at
            && $this->last_seen_at->greaterThan(now()->subMinutes(2));
    }
}
