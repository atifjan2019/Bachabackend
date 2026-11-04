<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ApiReferenceController;
use Illuminate\Support\Facades\Artisan;

Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return "All caches cleared!";
});

/*
|--------------------------------------------------------------------------
| Storefront (customer-facing)
|--------------------------------------------------------------------------
| Server-rendered Blade + Alpine. Public catalogue/content pages carry
| shared-cache headers; cart/checkout/auth/account pages never do.
*/

// Resized catalogue images (see ImageController). Outside the session
// middleware so the response carries no cookies and caches cleanly.
Route::get('img/{width}/{key}', [Shop\ImageController::class, 'show'])
    ->where(['width' => '[0-9]+|full', 'key' => '.+'])
    ->withoutMiddleware([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->name('shop.img');

Route::middleware('shop.cache')->group(function () {
    Route::get('/', [Shop\HomeController::class, 'index'])->name('shop.home');

    Route::get('/products', [Shop\ProductController::class, 'index'])->name('shop.products');
    Route::get('/products/{slug}', [Shop\ProductController::class, 'show'])->name('shop.product');

    // Categories no longer have their own pages — funnel old links into the shop filter.
    Route::redirect('/category', '/products', 301);
    Route::get('/category/{slug}', fn (string $slug) => redirect()->to('/products?category='.rawurlencode($slug), 301));

    Route::get('/about', [Shop\PageController::class, 'about'])->name('shop.about');
    Route::get('/contact', [Shop\PageController::class, 'contact'])->name('shop.contact');
    Route::get('/faq', [Shop\PageController::class, 'faq'])->name('shop.faq');
    Route::get('/privacy', [Shop\PageController::class, 'privacy'])->name('shop.privacy');
    Route::get('/terms', [Shop\PageController::class, 'terms'])->name('shop.terms');
    Route::get('/refund', [Shop\PageController::class, 'refund'])->name('shop.refund');
    Route::get('/shipping', [Shop\PageController::class, 'shipping'])->name('shop.shipping');

    Route::get('/blogs', [Shop\BlogController::class, 'index'])->name('shop.blogs');
    Route::get('/blogs/{slug}', [Shop\BlogController::class, 'show'])->name('shop.blog');

    Route::get('/sitemap.xml', [Shop\SeoController::class, 'sitemap'])->name('shop.sitemap');
    Route::get('/robots.txt', [Shop\SeoController::class, 'robots'])->name('shop.robots');
});

Route::get('/cart', [Shop\CartController::class, 'index'])->name('shop.cart');

Route::get('/checkout', [Shop\CheckoutController::class, 'index'])->name('shop.checkout');
Route::post('/checkout', [Shop\CheckoutController::class, 'store'])->name('shop.checkout.store');
Route::get('/checkout/success', [Shop\CheckoutController::class, 'success'])->name('shop.checkout.success');

// Customer auth (session guard "customer"); the API token guard is untouched.
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [Shop\AuthController::class, 'showLogin'])->name('shop.login');
    Route::post('/login', [Shop\AuthController::class, 'login'])->name('shop.login.store');
    Route::get('/register', [Shop\AuthController::class, 'showRegister'])->name('shop.register');
    Route::post('/register', [Shop\AuthController::class, 'register'])->name('shop.register.store');
    Route::get('/forgot-password', [Shop\AuthController::class, 'showForgot'])->name('shop.password.request');
    Route::post('/forgot-password', [Shop\AuthController::class, 'forgot'])->name('shop.password.email');
    Route::get('/reset-password', [Shop\AuthController::class, 'showReset'])->name('shop.password.reset');
    Route::post('/reset-password', [Shop\AuthController::class, 'reset'])->name('shop.password.update');
});
Route::post('/logout', [Shop\AuthController::class, 'logout'])->name('shop.logout');

Route::middleware('auth:customer')->prefix('account')->group(function () {
    Route::get('/', [Shop\AccountController::class, 'index'])->name('shop.account');
    Route::get('/orders', [Shop\AccountController::class, 'orders'])->name('shop.account.orders');
    Route::get('/orders/{reference}', [Shop\AccountController::class, 'order'])->name('shop.account.order');
    Route::get('/profile', [Shop\AccountController::class, 'profile'])->name('shop.account.profile');
    Route::put('/profile', [Shop\AccountController::class, 'updateProfile'])->name('shop.account.profile.update');
});

// Admin Auth Routes
Route::get('admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminLoginController::class, 'login']);
Route::post('admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::middleware(['auth:admin', 'admin.flush'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('products/{id}/quick', [ProductController::class, 'quick'])->name('products.quick');
    Route::post('products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class)->except(['create', 'store']);
    Route::post('orders/{id}/comments', [OrderController::class, 'addComment'])->name('orders.comments.store');
    Route::post('orders/{id}/comments/{comment}/resend', [OrderController::class, 'resendComment'])->name('orders.comments.resend');
    Route::patch('categories/{id}/quick', [CategoryController::class, 'quick'])->name('categories.quick');
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::resource('categories', CategoryController::class);
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'destroy']);
    Route::get('newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
    Route::get('newsletter/compose', [\App\Http\Controllers\Admin\NewsletterController::class, 'compose'])->name('newsletter.compose');
    Route::post('newsletter/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletter.send');
    Route::delete('newsletter/{id}', [\App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
    Route::get('reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{id}/toggle', [\App\Http\Controllers\Admin\ReviewController::class, 'toggle'])->name('reviews.toggle');
    Route::delete('reviews/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::resource('abandoned-carts', \App\Http\Controllers\Admin\AbandonedCartController::class)->only(['index', 'destroy']);
    Route::resource('contact-messages', \App\Http\Controllers\Admin\ContactMessageController::class)->only(['index', 'show', 'destroy']);
    Route::resource('blog', \App\Http\Controllers\Admin\BlogPostController::class);
    Route::resource('media', \App\Http\Controllers\Admin\MediaController::class)->only(['index', 'store', 'destroy']);
    Route::get('media-api/list', [\App\Http\Controllers\Admin\MediaController::class, 'apiList'])->name('media.api.list');
    Route::post('media-api/delete-r2', [\App\Http\Controllers\Admin\MediaController::class, 'deleteR2'])->name('media.api.deleteR2');
    Route::put('team/founder', [\App\Http\Controllers\Admin\TeamController::class, 'updateFounder'])->name('team.founder.update');
    Route::put('team/story', [\App\Http\Controllers\Admin\TeamController::class, 'updateStory'])->name('team.story.update');
    Route::post('team/reorder', [\App\Http\Controllers\Admin\TeamController::class, 'reorder'])->name('team.reorder');
    Route::resource('team', \App\Http\Controllers\Admin\TeamController::class)->except(['show']);
    Route::resource('admin-users', \App\Http\Controllers\Admin\AdminUserController::class)->except(['show']);
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('maintenance', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('maintenance/migrate', [\App\Http\Controllers\Admin\MaintenanceController::class, 'migrate'])->name('maintenance.migrate');
    Route::get('seo', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('seo.index');
    Route::put('seo', [\App\Http\Controllers\Admin\SeoController::class, 'update'])->name('seo.update');
    Route::get('api-reference', [ApiReferenceController::class, 'index'])->name('api-reference.index');
    Route::post('sync', function () {
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        return response()->json(['success' => true, 'message' => 'Cache cleared, frontend synced.']);
    })->name('sync');
});
