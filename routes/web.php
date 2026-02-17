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

    Route::patch('/admin/menu/{product}/toggle',
    [MenuController::class, 'toggle']
)->name('adminmenu.toggle');

    Route::get('/admin/menu/create',
        [MenuController::class, 'create'])->name('adminmenu.create');

    Route::post('/admin/menu',
        [MenuController::class, 'store'])->name('adminmenu.store');
        Route::patch(
    '/admin/menu/{product}/image',
    [MenuController::class, 'updateimgmodal']
)->name('adminmenu.updateimgmodal');

        Route::post('/admin/category',
    [MenuController::class, 'storeCategory'])
    ->name('adminmenu.category.store');

Route::delete('/admin/create/{id}',
    [MenuController::class, 'deleteCategory'])
    ->name('adminmenu.create.delete');

    Route::delete('/admin/menu/{id}',
    [MenuController::class, 'destroy'])->name('adminmenu.destroy');

    Route::put('/admin/menu/{id}',
    [MenuController::class, 'update'])->name('adminmenu.update');

    // routes/web.php
    Route::post('/admin/recipes', [MenuController::class, 'storemodal'])->name('adminmenu.storemodal');

    Route::delete('/admin/recipes/{recipe}', [MenuController::class, 'destroymodal'])->name('adminmenu.destroymodal');

    Route::delete('/admin/menu/{product}/category/{category}',[MenuController::class, 'detachCategory'])->name('adminmenu.category.detach');
    Route::post('/admin/menu/{product}/category',[MenuController::class, 'addCategory'])->name('adminmenu.category.add');
    Route::put('/admin/menu/{product}',[MenuController::class, 'updatemodal'])->name('adminmenu.updatemodal');
    Route::patch('/admin/menu/{product}',[MenuController::class, 'updateimgmodal'])->name('adminmenu.updateimgmodal');

    Route::get('/admin/stock',function(){
        return view('page.adminstock');
    })->name('adminstock');
});
