<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioProject extends Model
{
    protected $table = 'studio_projects';

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'timeline' => 'array',
        'brand_settings' => 'array',
        'generation_settings' => 'array',
        'last_opened_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(StudioMedia::class, 'project_id');
    }

    public function aiJobs()
    {
        return $this->hasMany(AIJob::class, 'project_id');
    }
}
