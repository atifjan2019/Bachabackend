<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon')->default('users');
            $table->string('image_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the current (previously hard-coded) team roles so the About page
        // renders identically until the admin edits them.
        $defaults = [
            ['Founder / CEO', 'Vision & leadership', 'crown'],
            ['Operations Manager', 'Fulfilment & logistics', 'clipboard'],
            ['Marketing Manager', 'Growth & brand reach', 'megaphone'],
            ['Customer Support Lead', 'Care & after-sales', 'headphones'],
            ['Creative / Brand Director', 'Design & identity', 'palette'],
        ];

        foreach ($defaults as $i => [$title, $subtitle, $icon]) {
            DB::table('team_members')->insert([
                'title'      => $title,
                'subtitle'   => $subtitle,
                'icon'       => $icon,
                'sort_order' => $i,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed founder details (stored as settings, editable from the same admin page).
        $founder = [
            'about_founder_name'     => 'Muhammad Ali Shah Bacha',
            'about_founder_role'     => 'Founder & CEO',
            'about_founder_bio'      => "Muhammad Ali Shah Bacha is the founder of Bacha Stylo Fashion, a fashion and lifestyle brand built on years of practical experience and market understanding. With over 11 years of involvement in the fashion and lifestyle industry, he has focused on continuous research, product selection, and learning from real market conditions to develop a strong sense of customer needs and trends.\n\nHis journey began at a very small level with limited resources, but through consistency, honesty, and customer satisfaction, he gradually built trust in the market — which became the foundation of the brand's growth.",
            'about_founder_image'    => '',
            'about_founder_initials' => 'MA',
        ];

        foreach ($founder as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');

        Setting::whereIn('setting_key', [
            'about_founder_name',
            'about_founder_role',
            'about_founder_bio',
            'about_founder_image',
            'about_founder_initials',
        ])->delete();
    }
};
