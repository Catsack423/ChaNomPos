<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // หน้าสำหรับ Admin: ดูสต็อกทั้งหมด, เพิ่มวัตถุดิบใหม่ และดู Log
    public function adminIndex()
    {
        $ingredients = Ingredient::with('inventory')->get();
        // ดึง Log ล่าสุด 50 รายการ พร้อมข้อมูลวัตถุดิบและผู้ใช้งาน
        $logs = InventoryLog::with(['ingredient', 'user'])->latest()->limit(50)->get();
        return view('page.adminstock', compact('ingredients', 'logs'));
    }
    public function staffIndex()
    {
        // ดึงข้อมูลวัตถุดิบพร้อมจำนวนสต็อกมาแสดง
        $ingredients = Ingredient::with('inventory')->get();
        
        // คืนค่าไปที่ไฟล์ resources/views/page/staffstock.blade.php
        return view('page.staffstock', compact('ingredients'));
    }

    // ระบบอัปเดตสต็อก (รองรับทั้งปุ่มบวก/ลบ และปุ่มบันทึกรวม)
    public function updateStock(Request $request)
    {
        $items = $request->ingredients;

        // เพิ่ม Validation ป้องกันเลขมหาศาล
        $request->validate([
            'ingredients.*.quantity' => 'required|numeric|between:-999999,999999',
        ]);

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                
                // ถ้าเป็น 0 ไม่ต้องทำอะไร
                if ($qty == 0) continue;

                $inventory = Inventory::where('ingredient_id', $item['ingredient_id'])->first();
                if (!$inventory) continue;

                // ใช้ increment เสมอ: 
                // ถ้า $qty เป็น 5 จะบวก 5
                // ถ้า $qty เป็น -5 จะบวกด้วย -5 (ซึ่งก็คือการลบนั่นเอง)
                $inventory->increment('quantity', $qty);

                // บันทึก Log
                InventoryLog::create([
                    'ingredient_id' => $item['ingredient_id'],
                    'user_id' => Auth::id(),
                    'action' => $qty > 0 ? 'add' : 'reduce', // แยกประเภทใน Log ตามเครื่องหมาย
                    'quantity' => abs($qty), // ใน Log ให้เก็บเป็นค่าบวกเสมอ (เพื่อให้อ่านง่าย)
                    'reason' => 'ปรับปรุงสต็อกด้วยตนเอง',
                    'created_at' => now()
                ]);
            }
        });

        return back()->with('success', 'อัปเดตสต็อกเรียบร้อยแล้ว');
    }
    public function deleteIngredient($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // ลบทั้งในตาราง inventories และ ingredients (Cascading)
                Inventory::where('ingredient_id', $id)->delete();
                InventoryLog::where('ingredient_id', $id)->delete();
                Ingredient::destroy($id);
            });
            return back()->with('success', 'ลบรายการวัตถุดิบเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีการใช้งานอยู่ในระบบ');
        }
    }

    // ฟังก์ชันสำหรับ Admin เพิ่มวัตถุดิบใหม่ (Table ingredients)
    public function storeIngredient(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'unit' => 'required',
            'initial_quantity' => 'required|numeric'
        ]);

        DB::transaction(function () use ($request) {
            $ingredient = Ingredient::create([
                'name' => $request->name,
                'unit' => $request->unit
            ]);

            Inventory::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $request->initial_quantity,
                'min_level' => 0
            ]);
        });

        return back()->with('success', 'เพิ่มวัตถุดิบใหม่แล้ว');
    }
}