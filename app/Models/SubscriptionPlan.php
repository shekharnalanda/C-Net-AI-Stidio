<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'limits' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
    ];
}
