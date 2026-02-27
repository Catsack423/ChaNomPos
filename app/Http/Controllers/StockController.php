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

        $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.ingredient_id' => 'required|exists:inventories,ingredient_id',
            'ingredients.*.quantity' => 'required|numeric|between:-999999,999999',
        ]);

        try {
            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty == 0) continue;

                    $inventory = Inventory::where('ingredient_id', $item['ingredient_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \Exception("ไม่พบรหัสวัตถุดิบ: " . $item['ingredient_id']);
                    }

                    // --- เงื่อนไขที่ 1: เช็คค่าจาก Database ก่อนเริ่มทำงาน ---
                    $currentInDB = (float)$inventory->quantity;

                    if ($currentInDB < 0) {
                        $currentInDB = 0; // ถ้าติดลบ ให้มองว่าเป็น 0 ทันที
                    }

                    // --- เงื่อนไขที่ 2: เช็คการทำงานตาม Request ---
                    if ($qty < 0) {
                        // กรณีสั่ง "ลด" (Negative quantity)
                        if ($currentInDB <= 0) {
                            // ถ้าใน DB เป็น 0 (หรือติดลบมาก่อน) แล้วสั่งลดอีก ให้ Error
                            throw new \Exception("วัตถุดิบ {$inventory->name} หมดสต็อกแล้ว ไม่สามารถลดเพิ่มได้");
                        }

                        // คำนวณการหักออก: ถ้าหัก 10 แต่มีแค่ 4 ให้หักแค่ 4
                        $actualChange = (abs($qty) > $currentInDB) ? -$currentInDB : $qty;
                        $newQty = $currentInDB + $actualChange;
                        $logQty = abs($actualChange); // บันทึกค่าที่หักจริงลง Log
                    } else {
                        // กรณีสั่ง "เพิ่ม" (Positive quantity)
                        $newQty = $currentInDB + $qty;
                        $logQty = $qty;
                    }

                    // บันทึกค่ากลับลง Database
                    $inventory->quantity = $newQty;
                    $inventory->save();

                    // บันทึก Log
                    InventoryLog::create([
                        'ingredient_id' => $item['ingredient_id'],
                        'user_id' => Auth::id(),
                        'action' => $qty > 0 ? 'add' : 'reduce',
                        'quantity' => $logQty,
                        'reason' => 'ปรับปรุงสต็อก (ระบบจัดการค่าติดลบและตัดสต็อกตามจริง)',
                        'created_at' => now()
                    ]);
                }
            });

            return back()->with('success', 'อัปเดตสต็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
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
