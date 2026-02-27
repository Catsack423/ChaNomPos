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
    // หน้าสำหรับ Admin: ดูสต็อกและ Log
    public function adminIndex()
    {
        $ingredients = Ingredient::with('inventory')->get();
        $logs = InventoryLog::with(['ingredient', 'user'])->latest()->limit(50)->get();
        
        return view('page.adminstock', compact('ingredients', 'logs'));
    }

    // หน้าสำหรับ Staff
    public function staffIndex()
    {
        $ingredients = Ingredient::with('inventory')->get();
        return view('page.staffstock', compact('ingredients'));
    }

    // ระบบอัปเดตสต็อก (รองรับปุ่มบวก/ลบ และการบันทึกรวม)
    public function updateStock(Request $request)
    {
        $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.ingredient_id' => 'required|exists:inventories,ingredient_id',
            'ingredients.*.quantity' => 'required|numeric|between:-999999,999999',
        ]);

        $items = $request->ingredients;

        try {
            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty == 0) continue;

                    $inventory = Inventory::where('ingredient_id', $item['ingredient_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \Exception("ไม่พบรหัสวัตถุดิบรหัส: " . $item['ingredient_id']);
                    }

                    $currentInDB = (float)$inventory->quantity;
                    if ($currentInDB < 0) $currentInDB = 0;

                    // จัดการกรณีสั่งลด (Negative quantity)
                    if ($qty < 0) {
                        if ($currentInDB <= 0) {
                            throw new \Exception("วัตถุดิบหมดสต็อกแล้ว ไม่สามารถลดเพิ่มได้");
                        }

                        // คำนวณการหักออกตามจริง (ถ้าสั่งหักมากกว่าที่มี ให้หักเท่าที่มี)
                        $actualChange = (abs($qty) > $currentInDB) ? -$currentInDB : $qty;
                        $newQty = $currentInDB + $actualChange;
                        $logQty = abs($actualChange);
                    } else {
                        // กรณีสั่งเพิ่ม
                        $newQty = $currentInDB + $qty;
                        $logQty = $qty;
                    }

                    $inventory->quantity = $newQty;
                    $inventory->save();

                    // บันทึก Log โดยใช้ 'add' หรือ 'reduce' ตามโครงสร้าง DB ของคุณ
                    InventoryLog::create([
                        'ingredient_id' => $item['ingredient_id'],
                        'user_id' => Auth::id(),
                        'action' => $qty > 0 ? 'add' : 'reduce',
                        'quantity' => $logQty,
                        'reason' => 'ปรับปรุงสต็อกด้วยตนเอง',
                        'created_at' => now()
                    ]);
                }
            });

            // ส่งกลับพร้อม Session Success เพื่อให้ SweetAlert ทำงาน
            return back()->with('success', 'ปรับปรุงสต็อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            // ส่งกลับพร้อม Session Error และข้อมูลที่กรอกค้างไว้
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ระบบลบวัตถุดิบ
    public function deleteIngredient($id)
    {
        try {
            $ingredient = Ingredient::find($id);
            if (!$ingredient) {
                return back()->with('error', 'ไม่พบรายการวัตถุดิบที่ต้องการลบ');
            }

            DB::transaction(function () use ($id) {
                Inventory::where('ingredient_id', $id)->delete();
                InventoryLog::where('ingredient_id', $id)->delete();
                Ingredient::destroy($id);
            });

            return back()->with('success', 'ลบรายการวัตถุดิบเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีการใช้งานอยู่ในสูตรอาหารหรือระบบอื่น');
        }
    }

    // ฟังก์ชันเพิ่มวัตถุดิบใหม่
    public function storeIngredient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'initial_quantity' => 'required|numeric|min:0'
        ]);

        try {
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

                // เพิ่มการบันทึก Log สำหรับสต็อกเริ่มต้น
                InventoryLog::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => Auth::id(),
                    'action' => 'add',
                    'quantity' => $request->initial_quantity,
                    'reason' => 'เพิ่มวัตถุดิบใหม่เข้าระบบ (สต็อกเริ่มต้น)',
                    'created_at' => now()
                ]);
            });

            return back()->with('success', "เพิ่มวัตถุดิบ '{$request->name}' สำเร็จ");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $e->getMessage());
        }
    }
}