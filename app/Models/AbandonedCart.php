<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'cart_data',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];
}

