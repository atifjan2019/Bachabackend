<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add merchandising flags so admins can curate the storefront:
     * featured products, best sellers, and a marketing label (badge).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_new');
            $table->boolean('is_best_seller')->default(false)->after('is_featured');
            // Badge shown on the card, e.g. "Trending Now", "Limited Edition".
            $table->string('label')->nullable()->after('is_best_seller');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_best_seller', 'label']);
        });
    }
};
