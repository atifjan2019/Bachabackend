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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('price')->nullable();
            $table->string('original_price')->nullable();
            $table->text('image')->nullable();
            $table->text('lifestyle')->nullable();
            $table->text('gallery')->nullable();
            $table->text('sizes')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->boolean('is_new')->default(false);
            $table->json('accordions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
