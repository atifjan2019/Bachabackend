<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderComment extends Model
{
    protected $fillable = [
        'order_id',
        'body',
        'emailed',
    ];

    protected $casts = [
        'emailed' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
