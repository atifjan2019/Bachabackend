<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the free-shipping switch so the storefront keeps its current behaviour
 * (free shipping ON above the existing threshold) until the admin changes it.
 * Does not touch any existing setting row.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            'free_shipping_enabled' => '1',
            'delivery_policy' => '',
        ] as $key => $value) {
            $exists = DB::table('settings')->where('setting_key', $key)->exists();
            if (!$exists) {
                DB::table('settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('setting_key', ['free_shipping_enabled', 'delivery_policy'])->delete();
    }
};
