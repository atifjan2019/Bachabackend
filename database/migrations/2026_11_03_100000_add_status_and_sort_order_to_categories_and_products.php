<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Let admins hide a category or product from the storefront (is_active) and
 * pin the order they appear in (sort_order).
 *
 * Everything that exists stays visible: is_active defaults to true. sort_order
 * is nullable on purpose — blank means "no manual position" and such rows list
 * after the numbered ones, so giving one product "1" really puts it first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('parent_id');
            $table->unsignedInteger('sort_order')->nullable()->after('is_active');
            $table->index(['is_active', 'sort_order'], 'categories_active_sort_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('label');
            $table->unsignedInteger('sort_order')->nullable()->after('is_active');
            $table->index(['is_active', 'sort_order'], 'products_active_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_active_sort_index');
            $table->dropColumn(['is_active', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_sort_index');
            $table->dropColumn(['is_active', 'sort_order']);
        });
    }
};
