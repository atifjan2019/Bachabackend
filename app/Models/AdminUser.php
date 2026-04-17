<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $fillable = [
        'username',
        'email',
        'password',
        'login_attempts',
        'locked_until',
        'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
        'last_login' => 'datetime',
        'login_attempts' => 'integer',
    ];
}

