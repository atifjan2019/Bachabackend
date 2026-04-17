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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'city')) {
                $table->string('city')->nullable()->after('shipping_address');
            }

            if (!Schema::hasColumn('orders', 'country')) {
                $table->string('country')->nullable()->after('city');
            }

            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('items');
            }

            if (!Schema::hasColumn('orders', 'shipping_fee')) {
                $table->decimal('shipping_fee', 10, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drop = [];

            foreach (['city', 'country', 'subtotal', 'shipping_fee', 'payment_method'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
