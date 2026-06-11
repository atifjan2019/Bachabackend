<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'price',
        'original_price',
        'image',
        'gallery',
        'sizes',
        'video_url',
        'is_new',
        'is_featured',
        'is_best_seller',
        'label',
        'accordions',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'accordions' => 'array',
        'gallery' => 'array',
        'sizes' => 'array',
    ];
}

