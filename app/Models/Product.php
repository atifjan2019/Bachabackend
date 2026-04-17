<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'price',
        'original_price',
        'image',
        'lifestyle',
        'gallery',
        'sizes',
        'video_url',
        'is_new',
        'accordions',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'accordions' => 'array',
    ];
}

