<?php

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

    Route::get('/storeedit',function(){
        return view('page.storeedit');
    })->name('storeedit');


    Route::get('/admin/dashboard',function(){
        return view('page.admindashboard');
    })->name('admindashboard');

    Route::get("/admin/menu",function(){
        return view('page.adminmenu');
    })->name('adminmenu');

    Route::get('/admin/stock',function(){
        return view('page.adminstock');
    })->name('adminstock');
});
