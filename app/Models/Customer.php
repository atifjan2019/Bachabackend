<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'phone',
        'address',
        'orders_count',
        'total_spent',
    ];

    protected $hidden = [
        'password',
        'api_token',
    ];

    protected $casts = [
        'orders_count' => 'integer',
        'total_spent' => 'decimal:2',
    ];
}
