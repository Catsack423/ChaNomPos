<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\AdminOrderController;
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
    // cart
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


    // orderhistory
    Route::get('/order', [OrderHistoryController::class, 'index'])
    ->name('orderhistory');
    
    Route::get('/admin/dashboard', [AdminOrderController::class, 'index'])
    ->name('admindashboard');

    //admin dashboard
    Route::prefix('admin')->group(function () {
        Route::get('/sales/{id}/edit', 
            [AdminOrderController::class, 'edit']
        )->name('admin.sales.edit');

        Route::put('/sales/{id}', 
            [AdminOrderController::class, 'update']
        )->name('admin.sales.update');

        Route::delete('/sales/{id}', 
            [AdminOrderController::class, 'destroy']
        )->name('admin.sales.destroy');
    });


    // ================= ADMIN MENU ROUTES (CRUD หลัก) =================
    
    // หน้าจัดการเมนู
    Route::get('/admin/menu', [MenuController::class, 'adminMenu'])->name('adminmenu');
    
    // เพิ่มเมนูใหม่
    Route::post('/admin/menu', [MenuController::class, 'store'])->name('adminmenu.store');
    
    // อัปเดตเมนู (รับข้อมูลจาก Modal แก้ไข)
    Route::put('/admin/menu/{id}', [MenuController::class, 'update'])->name('adminmenu.update');
    
    // ลบเมนู
    Route::delete('/admin/menu/{id}', [MenuController::class, 'destroy'])->name('adminmenu.destroy');
    
    // เปิด/ปิด การแสดงผลเมนู
    Route::patch('/admin/menu/{product}/toggle', [MenuController::class, 'toggle'])->name('adminmenu.toggle');
    Route::post('/admin/menu/{id}/activate', [MenuController::class, 'activate'])->name('adminmenu.activate');


    // ================= ADMIN MENU CATEGORY (AJAX) =================
    
    // AJAX สร้างหมวดหมู่
    Route::post('/adminmenu/category/ajax-store', [MenuController::class, 'ajaxStoreCategory'])->name('adminmenu.category.ajaxStore');
    
    // AJAX ลบหมวดหมู่
    Route::delete('/adminmenu/category/ajax-delete/{id}', [MenuController::class, 'ajaxDeleteCategory'])->name('adminmenu.category.ajaxDelete');




    
    // ================= OTHER =================
   

 

    Route::get('/admin/stock',function(){
        return view('page.adminstock');
    })->name('adminstock');

    Route::get('/staffstock', fn() => view('page.staffstock'))->name('storeedit');
   
   
});

    

