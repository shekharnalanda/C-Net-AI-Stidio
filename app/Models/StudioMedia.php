<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioMedia extends Model
{
    protected $table = 'studio_media';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(StudioProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
