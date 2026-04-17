<?php

use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['ok' => true]));

    Route::get('/settings', [ContentController::class, 'settings']);
    Route::get('/blog-posts', [ContentController::class, 'blogPosts']);
    Route::get('/blog-posts/{slug}', [ContentController::class, 'blogPost']);

    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/products', [CatalogController::class, 'products']);
    Route::get('/products/{slugOrId}', [CatalogController::class, 'product']);

    Route::post('/orders', [CheckoutController::class, 'storeOrder']);
    Route::post('/abandoned-carts', [CheckoutController::class, 'storeAbandonedCart']);
});
