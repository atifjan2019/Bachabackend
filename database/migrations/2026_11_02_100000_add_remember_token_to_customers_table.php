<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storefront customers sign in through a session guard now and can tick
 * "Keep me signed in"; Laravel stores that in remember_token.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'remember_token')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('remember_token', 100)->nullable()->after('api_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'remember_token')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
