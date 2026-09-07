<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIEngineProfile extends Model
{
    protected $guarded = [];
    protected $casts = ['capabilities' => 'array', 'configuration' => 'encrypted:array', 'is_active' => 'boolean'];
}
