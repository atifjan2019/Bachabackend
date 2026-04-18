<?php

use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\UploadController;
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

    Route::post('/upload', [UploadController::class, 'store']);

    // Customer Authentication & Management
    Route::post('/auth/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::get('/account/profile', [\App\Http\Controllers\Api\V1\AuthController::class, 'profile']);
        Route::patch('/account/profile', [\App\Http\Controllers\Api\V1\AuthController::class, 'updateProfile']);
        Route::get('/account/orders', [\App\Http\Controllers\Api\V1\AuthController::class, 'orders']);
        Route::get('/account/orders/{id}', [\App\Http\Controllers\Api\V1\AuthController::class, 'order']);
    });
});
