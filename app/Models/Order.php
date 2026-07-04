<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'city',
        'country',
        'items',
        'subtotal',
        'shipping_fee',
        'total_amount',
        'payment_method',
        'payment_receipt',
        'status',
    ];
    
    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
}

