<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIToolDocument extends Model
{
    protected $table = 'ai_tool_documents';

    protected $guarded = [];

    protected $casts = ['content' => 'array'];

    public function project() { return $this->belongsTo(StudioProject::class); }
    public function user() { return $this->belongsTo(User::class); }
}
