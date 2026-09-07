<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditEvent extends Model
{
    protected $guarded = [];
    protected $casts = ['context' => 'array'];
}
