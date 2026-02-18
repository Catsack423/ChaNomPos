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
    
    // ================= หน้าเพจทั่วไป =================
    Route::get('/dashboard', function () {
        return view('page.dashboard');
    })->name('dashboard');

    Route::get('/order',function(){
        return view('page.orderhistory');
    })->name('orderhistory');

    Route::get('/staffstock',function(){
        return view('page.staffstock');
    })->name('storeedit');

    Route::get('/admin/dashboard',function(){
        return view('page.admindashboard');
    })->name('admindashboard');

    Route::get('/admin/stock',function(){
        return view('page.adminstock');
    })->name('adminstock');


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

});