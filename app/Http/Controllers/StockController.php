<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Real_ingrediant;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class StockController extends Controller
{
    // หน้าสำหรับ Admin: ดูสต็อกและ Log
    public function adminIndex()
    {
        // 1. เคลียร์ล็อตที่หมดแล้วให้เป็น Soft Delete + เก็บ Log 'out'
        $allLots = Real_ingrediant::all();
        foreach ($allLots as $lot) {
            if ($lot->remaining() <= 0) {
                InventoryLog::create([
                    'real_ingredient_id' => $lot->id,
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'out',
                    'quantity' => 0,
                    'reason' => 'วัตถุดิบถูกใช้จนหมด (ตรวจสอบหน้า Admin Stock)',
                    'created_at' => now()
                ]);
                $lot->delete();
            }
        }

        $ingredients = Ingredient::with('real_ingredients')->get();

        $activeLots = Real_ingrediant::with(['product', 'ingredient'])
            ->where('in_use', 1)
            ->get();
        $products = Product::all();

        $stockLots = Real_ingrediant::with(['product', 'ingredient'])
            ->where('in_use', 0)
            ->get();

        $logs = InventoryLog::with(['real_ingredient.ingredient', 'user'])
            ->orderBy('id', 'desc') // แก้ปัญหาเวลาเพี้ยน
            ->limit(50)
            ->get();

        return view('page.adminstock', compact(
            'ingredients',
            'logs',
            'products',
            'activeLots',
            'stockLots'
        ));
    }

    // หน้าสำหรับ Staff
    public function staffIndex()
    {
        // 1. เคลียร์ล็อตที่หมดแล้วเช่นกัน
        $allLots = Real_ingrediant::all();
        foreach ($allLots as $lot) {
            if ($lot->remaining() <= 0) {
                InventoryLog::create([
                    'real_ingredient_id' => $lot->id,
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'out',
                    'quantity' => 0,
                    'reason' => 'วัตถุดิบถูกใช้จนหมด (ตรวจสอบหน้า Staff)',
                    'created_at' => now()
                ]);
                $lot->delete();
            }
        }

        $ingredients = Ingredient::with(['real_ingredients.logs'])->get();

        $activeLots = Real_ingrediant::with(['ingredient', 'logs'])
            ->where('in_use', 1)
            ->whereNull('deleted_at')
            ->get();

        $stockLots = Real_ingrediant::with('ingredient')
            ->where('in_use', 0)
            ->whereNull('deleted_at')
            ->get();

        $logs = InventoryLog::with(['real_ingredient.ingredient', 'user'])
            ->orderBy('id', 'desc') // แก้ปัญหาเวลาเพี้ยน
            ->take(10)
            ->get();

        return view('staffstock', compact(
            'ingredients',
            'activeLots',
            'stockLots',
            'logs'
        ));
    }

    // ระบบอัปเดตสต็อก (รองรับปุ่มบวก/ลบ และการบันทึกรวม)
    public function updateStock(Request $request)
    {
        $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|between:-999999,999999',
        ]);

        $items = $request->ingredients;

        try {
            DB::transaction(function () use ($items) {
                foreach ($items as $item) {

                    $qty = (float)($item['quantity'] ?? 0);

                    if ($qty == 0) continue;

                    $lot = Real_ingrediant::where('ingredient_id', $item['ingredient_id'])
                        ->where('in_use', 1)
                        ->first();

                    if (!$lot) {
                        throw new \Exception("ไม่พบ LOT ที่เปิดใช้งานสำหรับรหัสวัตถุดิบ: " . $item['ingredient_id']);
                    }

                    // แก้ Logic การปรับสต็อกเพื่อป้องกันบั๊กหักเบิ้ล 2 รอบ
                    if ($qty > 0) {
                        // กรณีเพิ่ม (+): บวกเข้าฐาน Quantity แล้วสร้าง Log add 
                        $lot->quantity += $qty;
                        $lot->save();
                        $action = 'add';
                    } else {
                        // กรณีลด (-): ห้ามแก้ฐาน Quantity! ให้สร้างเฉพาะ Log reduce 
                        // เพราะฟังก์ชัน remaining() จะเอาไปหักลบออกให้เองโดยอัตโนมัติ
                        $action = 'reduce';
                    }

                    // บันทึก Log การปรับสต็อก
                    InventoryLog::create([
                        'real_ingredient_id' => $lot->id,
                        'user_id' => Auth::id(),
                        'action' => $action,
                        'quantity' => abs($qty),
                        'reason' => 'ปรับปรุงสต็อก (Manual)',
                        'created_at' => now()
                    ]);

                    // ตรวจสอบทันทีว่าปรับลดแล้วของหมดไหม ถ้าหมดให้เคลียร์ทิ้ง
                    if ($lot->remaining() <= 0) {
                        InventoryLog::create([
                            'real_ingredient_id' => $lot->id,
                            'user_id' => Auth::id(),
                            'action' => 'out',
                            'quantity' => 0,
                            'reason' => 'วัตถุดิบหมดจากการปรับปรุงสต็อก',
                            'created_at' => now()
                        ]);
                        $lot->delete();
                    }
                }
            });

            return back()->with('success', 'ปรับปรุงสต็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
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
                // ถ้ามี Real_ingrediant ที่ผูกไว้ อาจจะต้องการลบด้วย 
                // Real_ingrediant::where('ingredient_id', $id)->delete();
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
            'unit' => 'required|string|max:50'
        ]);

        try {
            Ingredient::create([
                'name' => $request->name,
                'unit' => $request->unit
            ]);

            return back()->with('success', "เพิ่มวัตถุดิบ '{$request->name}' สำเร็จ");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function useIngredient($id)
    {
        $ingredient = Real_ingrediant::findOrFail($id);

        $ingredient->in_use = 1;
        // คำนวณวันหมดอายุหลังจากเปิดใช้งาน 2 วัน
        $ingredient->expried = \Carbon\Carbon::now()->addDays(2);

        $ingredient->save();

        return back()->with('success', 'เปิดใช้งานวัตถุดิบแล้ว และตั้งวันหมดอายุเป็น 2 วันนับจากนี้');
    }

    public function addLot(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:1',
            'expired' => 'required|date',
            'imgurl' => 'nullable|image'
        ]);

        $img = null;

        if ($request->hasFile('imgurl')) {
            $file = $request->file('imgurl');
            $img = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img'), $img);
        }

        $lot = Real_ingrediant::create([
            'name' => $request->name,
            'ingredient_id' => $request->ingredient_id,
            'quantity' => $request->quantity,
            'imgurl' => $img,
            'expried' => $request->expired,
            'in_use' => 0
        ]);

        InventoryLog::create([
            'real_ingredient_id' => $lot->id,
            'user_id' => Auth::id(),
            'action' => 'import',
            'quantity' => $lot->quantity,
            'reason' => 'นำเข้าสินค้า Lot #' . $lot->id,
            'created_at' => now()
        ]);

        return response()->json([
            'status' => 'success'
        ]);
    }
}