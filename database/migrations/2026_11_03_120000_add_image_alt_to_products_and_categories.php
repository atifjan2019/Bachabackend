<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-written ALT text for images (SEO handoff E1). Blank keeps today's
 * behaviour: the product/category name is used.
 *
 * products.gallery_alts is a JSON list aligned with the gallery order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('image');
            $table->json('gallery_alts')->nullable()->after('gallery');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_alt', 'gallery_alts']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image_alt');
        });
    }
};
