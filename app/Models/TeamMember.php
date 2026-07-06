<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'bio',
        'icon',
        'image_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Icon keys the admin can choose from. The frontend maps each key to a
     * lucide-react icon component (see lib/api/about.ts / about page).
     */
    public const ICONS = [
        'crown', 'clipboard', 'megaphone', 'headphones', 'palette',
        'users', 'star', 'gem', 'shield', 'landmark',
        'repeat', 'heart', 'sparkles', 'truck', 'tag', 'briefcase',
    ];
}
