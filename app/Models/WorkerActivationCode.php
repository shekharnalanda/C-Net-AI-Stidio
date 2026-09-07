<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerActivationCode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function worker()
    {
        return $this->belongsTo(AIWorker::class, 'worker_id');
    }
}
