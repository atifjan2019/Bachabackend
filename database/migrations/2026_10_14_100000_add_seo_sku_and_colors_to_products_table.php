<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Additive only. Every existing product row is kept exactly as it is; the new
 * columns start empty except for `sku`, which is backfilled from the id so
 * every product has a code from the first page load after this runs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 64)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('products', 'colors')) {
                $table->text('colors')->nullable()->after('sizes');
            }
        });

        // Backfill a code for every product that has none. Plain updates by
        // id, nothing else on the row is touched.
        DB::table('products')
            ->where(function ($q) {
                $q->whereNull('sku')->orWhere('sku', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('products')
                        ->where('id', $row->id)
                        ->update(['sku' => 'BS-' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT)]);
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->index('sku', 'products_sku_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_sku_index');
            $table->dropColumn(['sku', 'meta_title', 'meta_description', 'colors']);
        });
    }
};
