<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('abandoned_carts', function (Blueprint $table) {
            if (!Schema::hasColumn('abandoned_carts', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('abandoned_carts', 'total')) {
                $table->decimal('total', 10, 2)->default(0)->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abandoned_carts', function (Blueprint $table) {
            foreach (['name', 'total'] as $column) {
                if (Schema::hasColumn('abandoned_carts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
