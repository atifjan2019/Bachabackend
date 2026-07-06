<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'about_story_heading'  => 'Rooted in tradition,',
            'about_story_accent'   => 'built on trust.',
            'about_story_body'     => "Bacha Stylo started from Lower Dir, KPK with a simple vision — to create a trusted fashion identity rooted in tradition, honesty, and quality.\n\nWhat began with traditional wear has now grown into a broader lifestyle brand offering clothes, waistcoats, Chitrali pakols, caps, shawls, fragrances, footwear, and personal care products. Every product reflects our belief in authenticity, fair pricing, and customer satisfaction.",
            'about_story_location' => 'Lower Dir, KPK',
            'about_story_image'    => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }

    public function down(): void
    {
        Setting::whereIn('setting_key', [
            'about_story_heading', 'about_story_accent', 'about_story_body',
            'about_story_location', 'about_story_image',
        ])->delete();
    }
};
