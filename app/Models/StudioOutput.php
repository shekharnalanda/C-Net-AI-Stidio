<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioOutput extends Model
{
    protected $table = 'studio_outputs';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(StudioProject::class, 'project_id');
    }

    public function job()
    {
        return $this->belongsTo(AIJob::class, 'ai_job_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
