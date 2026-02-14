<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // ================= USER =================
    Route::get('/dashboard', [SaleController::class, 'index'])
        ->name('dashboard');

    Route::post('/add-to-cart', [SaleController::class, 'addToCart'])
        ->name('cart.add');

    Route::post('/cart/increase', [SaleController::class, 'increase'])
        ->name('cart.increase');

    Route::post('/cart/decrease', [SaleController::class, 'decrease'])
        ->name('cart.decrease');

    Route::post('/cart/remove', [SaleController::class, 'remove'])
        ->name('cart.remove');

    Route::post('/checkout', [SaleController::class, 'checkout'])
        ->name('checkout');

    // ================= OTHER =================
    Route::get('/order', fn() => view('page.orderhistory'))->name('orderhistory');
    Route::get('/staffstock', fn() => view('page.staffstock'))->name('storeedit');
    Route::get('/admin/dashboard', fn() => view('page.admindashboard'))->name('admindashboard');
    Route::get('/admin/menu', fn() => view('page.adminmenu'))->name('adminmenu');
    Route::get('/admin/stock', fn() => view('page.adminstock'))->name('adminstock');
});
