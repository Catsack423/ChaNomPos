<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController; // อย่าลืม Use Controller นะครับ

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

    Route::get('/order', function () {
        return view('page.orderhistory');
    })->name('orderhistory');

    // --- ส่วนของ Staff Stock ---
    // เปลี่ยนจาก view() เป็นเรียก method ใน Controller เพื่อดึงข้อมูล Inventory
    Route::get('/staffstock', [StockController::class, 'staffIndex'])->name('staffstock');
    // --- ส่วนของ Admin ---
    Route::get('/admin/dashboard', function () {
        return view('page.admindashboard');
    })->name('admindashboard');

    Route::get("/admin/menu", function () {
        return view('page.adminmenu');
    })->name('adminmenu');

    // ส่วนของ Admin Stock (ดึงทั้งสต็อกและ Logs)
    Route::get('/admin/stock', [StockController::class, 'adminIndex'])->name('adminstock');
    Route::delete('/admin/stock/delete/{id}', [StockController::class, 'deleteIngredient'])->name('admin.stock.delete');

    // --- ส่วนของ Action (POST) สำหรับจัดการข้อมูล ---
    // Route สำหรับกดปุ่ม เพิ่ม/ลด สต็อก (ใช้ร่วมกันทั้ง Staff/Admin)
    Route::post('/stock/update', [StockController::class, 'updateStock'])->name('stock.update');
    Route::post('/admin/stock/add', [StockController::class, 'storeIngredient'])->name('admin.stock.add');
    
    // Route สำหรับ Admin เพิ่มวัตถุดิบใหม่เข้า Table ingredients
    Route::post('/admin/stock/add', [StockController::class, 'storeIngredient'])->name('admin.stock.add');
});