<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}

