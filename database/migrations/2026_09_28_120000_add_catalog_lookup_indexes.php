<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index the columns the public catalog API filters on.
 *
 * `products` and `categories` carried only a PRIMARY key, so every storefront
 * read was a full table scan: the category filter, the slug lookup behind
 * /products/{slug}, and each merchandising flag used by the collection tabs.
 * That matches the captured API timings, where even a near-empty response took
 * over a second — the cost was in finding the rows, not sending them.
 *
 * Indexes only, no column or data changes: query results are identical.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Catalog listing filters by the category slug — now expanded to a
            // whole subtree, so this is a whereIn over several values.
            $table->index('category', 'products_category_index');
            // /api/v1/products/{slugOrId} and the admin's slug uniqueness check.
            $table->index('slug', 'products_slug_index');
            // The "Featured / Best Sellers / New Arrivals" collection tabs.
            $table->index('is_featured', 'products_is_featured_index');
            $table->index('is_best_seller', 'products_is_best_seller_index');
            $table->index('is_new', 'products_is_new_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Walking the category tree to expand a parent slug, and the
            // Category::products() relation, both key off the slug.
            $table->index('slug', 'categories_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_index');
            $table->dropIndex('products_slug_index');
            $table->dropIndex('products_is_featured_index');
            $table->dropIndex('products_is_best_seller_index');
            $table->dropIndex('products_is_new_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_slug_index');
        });
    }
};
