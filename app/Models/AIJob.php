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
        'available_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(StudioProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function outputs()
    {
        return $this->hasMany(StudioOutput::class, 'ai_job_id');
    }

}
