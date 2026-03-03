<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $today = Carbon::today();

            $query = Sale::with(['items.product'])
                ->whereDate('sold_at', $today);

            if ($request->filled('search')) {

                $search = trim($request->search);

                $query->where(function ($q) use ($search) {

                    // 🔥 ค้นหา ID แบบปกติ
                    $q->orWhere('id', 'like', '%' . str_replace('#', '', $search) . '%');

                    // 🔥 ค้นหาชื่อเมนู
                    $q->orWhereHas('items.product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });

                });
            }
               
            $sales = $query->orderBy('sold_at', 'desc')
               ->orderBy('id', 'desc')
               ->paginate(10)
               ->withQueryString();
            $totalSalesAmount = (clone $query)->sum('total_price');

            return view('page.orderhistory', compact('sales', 'totalSalesAmount'));

        } catch (\Exception $e) {
            return back()->with('error', 'ไม่สามารถโหลดข้อมูลประวัติการขายได้: ' . $e->getMessage());
        }
    }
    /**
     * ฟังก์ชันสำหรับยกเลิกออเดอร์ (คืนสต็อก + บันทึก Log + ลบรายการ)
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. ดึงข้อมูลการขายพร้อมรายการสินค้าและสูตร (Recipes)
                $sale = Sale::with('items.product.recipes.ingredient')->findOrFail($id);

                foreach ($sale->items as $item) {
                    $product = $item->product;
                    
                    // หากสินค้ายังมีข้อมูลสูตร ให้ทำการคืนสต็อก
                    if ($product && $product->recipes) {
                        foreach ($product->recipes as $recipe) {
                            $restoreAmount = $recipe->amount * $item->quantity;

                            // คืนค่ากลับเข้าตาราง inventories
                            $inventory = Inventory::where('ingredient_id', $recipe->ingredient_id)->first();
                            
                            if ($inventory) {
                                $inventory->increment('quantity', $restoreAmount);

                                // บันทึก Log การคืนสต็อก (Action: add)
                                InventoryLog::create([
                                    'ingredient_id' => $recipe->ingredient_id,
                                    'user_id' => Auth::id(),
                                    'action' => 'add', // ใช้ 'add' ตามโครงสร้าง DB ของคุณ
                                    'quantity' => $restoreAmount,
                                    'reason' => "ยกเลิกออเดอร์: #{$sale->id} (คืนสต็อกจากสินค้า {$product->name})",
                                    'created_at' => now()
                                ]);
                            }
                        }
                    }
                }

                // 2. ลบข้อมูลการขาย (ใช้ SoftDeletes ตามที่โมเดล Sale มีอยู่)
                $sale->delete();
            });

            return back()->with('success', 'ยกเลิกออเดอร์และคืนสต็อกวัตถุดิบเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการยกเลิกออเดอร์: ' . $e->getMessage());
        }
    }
}