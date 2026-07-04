<?php

use App\Models\Order;
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
            if (!Schema::hasColumn('orders', 'reference')) {
                $table->string('reference', 20)->nullable()->unique()->after('id');
            }
        });

        // Backfill non-sequential references for existing orders.
        Order::whereNull('reference')->get()->each(function (Order $order) {
            $order->reference = Order::generateReference();
            $order->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'reference')) {
                $table->dropColumn('reference');
            }
        });
    }
};
