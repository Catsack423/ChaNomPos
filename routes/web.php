<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route("dashboard");
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('page.dashboard');
    })->name('dashboard');

    Route::get('/order',function(){
        return view('page/orderhistory');
    })->name('orderhistory');

    Route::get('/staffstock',function(){
        return view('page.staffstock');
    })->name('storeedit');


    Route::get('/admin/dashboard',function(){
        return view('page.admindashboard');
    })->name('admindashboard');


    Route::get('/admin/menu', [MenuController::class, 'adminMenu'])->name('adminmenu');

    Route::post('/admin/menu/{id}/activate', [MenuController::class, 'activate'])
    ->name('adminmenu.activate');

    Route::get('/admin/menu/create',
        [MenuController::class, 'create'])->name('adminmenu.create');

    Route::post('/admin/menu',
        [MenuController::class, 'store'])->name('adminmenu.store');

    Route::delete('/admin/menu/{id}',
    [MenuController::class, 'destroy'])->name('adminmenu.destroy');

    Route::put('/admin/menu/{id}',
    [MenuController::class, 'update'])->name('adminmenu.update');

    Route::get('/admin/stock',function(){
        return view('page.adminstock');
    })->name('adminstock');
});
