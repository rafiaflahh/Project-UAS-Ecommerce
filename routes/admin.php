<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SalesStatisticsController;
use App\Http\Controllers\Admin\TransactionManagementController;

// Admin Routes (protected)
Route::middleware(['admin', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('stocks', StockController::class);
    Route::post('stocks/bulk-update', [StockController::class, 'bulkUpdate'])->name('stocks.bulk-update');
    Route::resource('transactions', TransactionManagementController::class);
    Route::get('transactions/report', [TransactionManagementController::class, 'report'])->name('transactions.report');
    Route::post('transactions/{transaction}/cancel', [TransactionManagementController::class, 'cancel'])->name('transactions.cancel');
    Route::post('transactions/{transaction}/update-status', [TransactionManagementController::class, 'updateStatus'])->name('transactions.updateStatus');
    Route::put('transactions/{transaction}/update-status', [TransactionManagementController::class, 'updateStatus'])->name('transactions.updateStatus.put');
    Route::get('statistics', [SalesStatisticsController::class, 'index'])->name('statistics.index');
    Route::get('statistics/report', [SalesStatisticsController::class, 'report'])->name('statistics.report');
    Route::get('statistics/export', [SalesStatisticsController::class, 'export'])->name('statistics.export');
    Route::get('statistics/best-products', [SalesStatisticsController::class, 'bestProducts'])->name('statistics.bestProducts');
    Route::get('statistics/daily', [SalesStatisticsController::class, 'dailyStats'])->name('statistics.daily');
    Route::get('statistics/monthly', [SalesStatisticsController::class, 'monthlyStats'])->name('statistics.monthly');
    Route::get('statistics/yearly', [SalesStatisticsController::class, 'yearlyStats'])->name('statistics.yearly');
    Route::get('statistics/categories', [SalesStatisticsController::class, 'categoryPerformance'])->name('statistics.categories');
});