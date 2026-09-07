<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioUsageEvent extends Model
{
    protected $guarded = [];
    protected $casts = ['metadata' => 'array'];
}
