<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'trial_started_at',
        'trial_ends_at',
        'last_login_at',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'master_admin'], true);
    }

    public function isMasterAdmin(): bool
    {
        return $this->role === 'master_admin';
    }

    public function isTrialActive(): bool
    {
        return $this->trial_ends_at !== null
            && now()->lessThan($this->trial_ends_at);
    }

    public function projects()
    {
        return $this->hasMany(StudioProject::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
