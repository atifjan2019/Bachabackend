<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Reduce a Google site-verification value to the bare content token.
     *
     * Search Console hands out a full meta tag; only its content attribute
     * belongs in the setting. Accepts either form and returns the token.
     */
    public static function verificationToken(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $value, $m)) {
            return trim($m[1]);
        }

        return $value;
    }
}
