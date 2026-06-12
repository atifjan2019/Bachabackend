<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editorial + full SEO fields so admins can publish Journal posts
     * with proper search-engine and social-share metadata.
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('content');
            $table->string('category')->nullable()->after('excerpt');
            $table->string('meta_title')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->string('canonical_url')->nullable()->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'excerpt',
                'category',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'og_image',
                'canonical_url',
            ]);
        });
    }
};
