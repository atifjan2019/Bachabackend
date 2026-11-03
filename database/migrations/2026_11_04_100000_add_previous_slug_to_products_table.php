<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When an admin changes a product's slug, the old one is kept here so the
 * old URL can 301 to the new one instead of dying (links in Google, social
 * posts and WhatsApp chats keep working).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('previous_slug')->nullable()->after('slug');
            $table->index('previous_slug', 'products_previous_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_previous_slug_index');
            $table->dropColumn('previous_slug');
        });
    }
};
