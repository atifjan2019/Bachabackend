<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ApiReferenceController;

Route::get('/', function () {
    // This backend doesn't have a storefront anymore
    return redirect()->route('admin.login');
});

// Admin Auth Routes
Route::get('admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminLoginController::class, 'login']);
Route::post('admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class)->except(['create', 'store']);
    Route::resource('categories', CategoryController::class);
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'destroy']);
    Route::resource('abandoned-carts', \App\Http\Controllers\Admin\AbandonedCartController::class)->only(['index', 'destroy']);
    Route::resource('blog', \App\Http\Controllers\Admin\BlogPostController::class);
    Route::resource('media', \App\Http\Controllers\Admin\MediaController::class)->only(['index', 'store', 'destroy']);
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('seo', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('seo.index');
    Route::put('seo', [\App\Http\Controllers\Admin\SeoController::class, 'update'])->name('seo.update');
    Route::get('api-reference', [ApiReferenceController::class, 'index'])->name('api-reference.index');
});
