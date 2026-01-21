<?php

use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', function () {
    return view('welcome');
});

// Shop Routes
Route::get('/shop', [\App\Http\Controllers\ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/product/{product}', [\App\Http\Controllers\ShopController::class, 'show'])->name('shop.show');
Route::get('/shop/category/{category}', [\App\Http\Controllers\ShopController::class, 'category'])->name('shop.category');

// Cart Routes
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{product}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{transaction}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');

// Admin Routes
Route::middleware(['admin', 'auth'])->prefix('admin')->name('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    });
    
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('stocks', \App\Http\Controllers\Admin\StockController::class);
    Route::get('/statistics', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'index'])->name('admin.statistics.index');
    Route::get('/statistics/report', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'report'])->name('admin.statistics.report');
    Route::get('/statistics/export', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'export'])->name('admin.statistics.export');
    Route::get('/statistics/best-products', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'bestProducts'])->name('admin.statistics.bestProducts');
    Route::get('/statistics/monthly', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'monthlyStats'])->name('admin.statistics.monthly');
    Route::get('/statistics/yearly', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'yearlyStats'])->name('admin.statistics.yearly');
    Route::get('/statistics/categories', [\App\Http\Controllers\Admin\SalesStatisticsController::class, 'categoryPerformance'])->name('admin.statistics.categories');
});



// Admin Dashboard
Route::middleware(['auth', 'admin'])->get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

// Transaction Routes (User)
Route::middleware('auth')->prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TransactionController::class, 'index'])->name('index');
    Route::get('/{transaction}', [\App\Http\Controllers\TransactionController::class, 'show'])->name('show');
});

// Auth routes
require __DIR__.'/auth.php';

// Admin routes
require __DIR__.'/admin.php';