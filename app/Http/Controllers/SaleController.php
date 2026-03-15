<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory; // เก็บไว้เผื่อมีการเรียกใช้ที่อื่น
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * ฟังก์ชันตรวจสอบว่าวัตถุดิบพอผลิตสินค้าตามจำนวนที่ระบุหรือไม่
     */
    private function hasEnoughStock($productId, $quantityRequested)
    {
        // โหลดข้อมูลสินค้าและสูตร (เอา .ingredient.inventory แบบเก่าออก)
        $product = Product::with('recipes')->find($productId);

        if (!$product) return false;

        // ถ้าไม่มีสูตร ให้ถือว่า "มีของ" (Pass)
        if ($product->recipes->isEmpty()) {
            return true;
        }

        foreach ($product->recipes as $recipe) {
            // ดึงล็อตทั้งหมดของวัตถุดิบตัวนี้ (ที่ยังไม่ถูก Soft Delete)
            $lots = \App\Models\Real_ingrediant::where('ingredient_id', $recipe->ingredient_id)->get();

            // รวมจำนวนคงเหลือทั้งหมด (ทั้งเปิดใช้แล้วและยังอยู่ในคลัง)
            $totalRemaining = $lots->sum(function ($lot) {
                return $lot->remaining();
            });

            // คำนวณปริมาณที่ต้องใช้ทั้งหมด
            $neededAmount = $recipe->amount * $quantityRequested;

            // ถ้าวัตถุดิบตัวใดตัวหนึ่งมีไม่พอ ให้คืนค่า false ทันที
            if ($totalRemaining < $neededAmount) {
                return false;
            }
        }

        return true;
    }

    /**
     * หน้าแรก Dashboard
     */
    public function index()
    {
        // --- 0. ตรวจสอบและจัดการลบล็อตวัตถุดิบที่หมดแล้ว (Soft Delete) ---
        // ทำก่อนเสมอ เพื่อให้การคำนวณสต็อกในตะกร้าและสถานะสินค้าแม่นยำที่สุด
        $allLots = \App\Models\Real_ingrediant::all();
        foreach ($allLots as $lot) {
            if ($lot->remaining() <= 0) {
                // เก็บ Log เป็น 'out' ก่อนลบ เพื่อบันทึกว่าวัตถุดิบหมด
                \App\Models\InventoryLog::create([
                    'real_ingredient_id' => $lot->id,
                    'user_id' => Auth::id() ?? 1, // ป้องกัน Error กรณี System รัน
                    'action' => 'out',
                    'quantity' => 0, // ค่าเป็น 0 เพื่อแสดงสถานะหมด
                    'reason' => "วัตถุดิบถูกใช้จนหมด (ตรวจสอบหน้า Dashboard)",
                    'created_at' => now()
                ]);

                $lot->delete();
            }
        }

        // 1. ตรวจสอบสต็อกและอัปเดตสถานะ is_active (เอา .inventory แบบเก่าออก)
        $allProducts = Product::all();

        foreach ($allProducts as $product) {
            $canMake = $this->hasEnoughStock($product->id, 1);

            // อัปเดตเฉพาะตอนที่สถานะเปลี่ยน เพื่อลดภาระ Database
            if ($product->is_active != $canMake) {
                $product->update(['is_active' => $canMake ? 1 : 0]);
            }
        }

        // 2. ดึงข้อมูลสินค้าที่ต้องทั้ง Active (สต็อกพอ) และ Show (Admin สั่งแสดง)
        $products = Product::where('is_active', 1)->where('is_show', 1)->get();
        $categories = Category::all();
        $cart = session()->get('cart', []);

        // 3. กรองสินค้าในตะกร้า (ถ้าสินค้าถูกปิด is_active หรือถูกซ่อน is_show ให้เอาออก)
        if (!empty($cart)) {
            $cartChanged = false;
            foreach ($cart as $id => $item) {
                $productInCart = Product::find($id);

                // ตรวจสอบ: ต้องมีสินค้า + ต้อง Active + ต้อง Show + สต็อกต้องพอตามจำนวนในตะกร้า
                if (!$productInCart || !$productInCart->is_active || !$productInCart->is_show || !$this->hasEnoughStock($id, $item['quantity'])) {
                    unset($cart[$id]);
                    $cartChanged = true;
                }
            }
            if ($cartChanged) {
                session()->put('cart', $cart);
            }
        }

        return view('page.dashboard', compact('products', 'cart', 'categories'));
    }

    private function responseCart($cart, $message = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'cart' => $cart,
            'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
        ]);
    }

    public function addToCart(Request $request)
    {
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'สินค้าไม่พร้อมใช้งาน'], 400);
        }

        $cart = session()->get('cart', []);

        // ตรวจสอบสถานะสินค้า (Active / Show)
        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        // คำนวณจำนวนใหม่ที่จะเพิ่มลงตะกร้า
        $currentQty = $cart[$product->id]['quantity'] ?? 0;
        $newQty = $currentQty + 1;

        // เช็คว่าวัตถุดิบพอสำหรับจำนวนใหม่หรือไม่ (ฟังก์ชันนี้ใช้ลอจิกรวมล็อตใหม่แล้ว)
        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json(['status' => 'error', 'message' => 'วัตถุดิบไม่เพียงพอสำหรับจำนวนนี้'], 400);
        }

        // อัปเดตข้อมูลตะกร้า
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $newQty;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1
            ];
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart, "เพิ่ม '{$product->name}' ลงตะกร้าแล้ว");
    }

    public function increase(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        $currentQty = $cart[$product->id]['quantity'] ?? 0;
        $newQty = $currentQty + 1;

        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถเพิ่มได้เนื่องจากวัตถุดิบไม่พอ'], 400);
        }

        $cart[$product->id]['quantity'] = $newQty;
        session()->put('cart', $cart);

        return $this->responseCart($cart, "เพิ่มจำนวน '{$product->name}' เรียบร้อย");
    }

    public function decrease(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']--;

            // ถ้าลดจนเหลือ 0 หรือน้อยกว่า ให้ลบออกจากตะกร้าไปเลย
            if ($cart[$product->id]['quantity'] <= 0) {
                unset($cart[$product->id]);
            }

            // อัปเดตเซสชันเมื่อมีการเปลี่ยนแปลงเท่านั้น
            session()->put('cart', $cart);
        }

        return $this->responseCart($cart, "ลดจำนวนสินค้าเรียบร้อย");
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return $this->responseCart($cart, "ลบสินค้าออกจากตะกร้าแล้ว");
    }

    /**
     * ชำระเงิน ตรวจสอบด่านสุดท้าย (ต้อง Active และ Show)
     */
    public function checkout()
    {
        $cart = session()->get('cart');
        if (!$cart) return back()->with('error', 'ไม่มีสินค้าในตะกร้า');

        // --- 1. ตรวจสอบวัตถุดิบรวม "ทั้งตะกร้า" ก่อนเริ่มการขาย ---
        $requiredIngredients = [];
        foreach ($cart as $id => $item) {
            $product = Product::with('recipes')->find($id);

            // เช็คสถานะพื้นฐานของสินค้า
            if (!$product || !$product->is_active || !$product->is_show) {
                return back()->with('error', "สินค้า " . ($product->name ?? 'บางรายการ') . " ไม่พร้อมจำหน่ายหรือถูกซ่อน");
            }

            // รวมจำนวนวัตถุดิบที่ต้องใช้ทั้งหมดในออเดอร์นี้
            foreach ($product->recipes as $recipe) {
                $ingredientId = $recipe->ingredient_id;
                $amountNeeded = $recipe->amount * $item['quantity'];

                if (!isset($requiredIngredients[$ingredientId])) {
                    $requiredIngredients[$ingredientId] = 0;
                }
                $requiredIngredients[$ingredientId] += $amountNeeded;
            }
        }

        // เช็คยอดรวมเทียบกับสต็อกจริง
        foreach ($requiredIngredients as $ingredientId => $totalNeeded) {
            $lots = \App\Models\Real_ingrediant::where('ingredient_id', $ingredientId)->get();
            $totalAvailable = $lots->sum(function ($lot) {
                return $lot->remaining();
            });

            if ($totalAvailable < $totalNeeded) {
                // ถ้ามีวัตถุดิบตัวไหนไม่พอ ให้ยกเลิกทั้งบิล
                return back()->with('error', 'วัตถุดิบในคลังไม่เพียงพอสำหรับคำสั่งซื้อทั้งหมดในตะกร้า');
            }
        }

        // --- 2. เริ่ม Transaction บันทึกการขายและตัดสต็อก ---
        return DB::transaction(function () use ($cart) {
            // สร้างบิลการขาย
            $total = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
            $sale = \App\Models\Sale::create([
                'user_id' => Auth::id(),
                'total_price' => $total,
                'sold_at' => now()
            ]);

            foreach ($cart as $id => $item) {
                // บันทึกรายการสินค้าที่ขาย
                \App\Models\SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // ดึงสูตรเพื่อมาตัดสต็อกทีละล็อต
                $productRecord = Product::with('recipes')->find($id);

                foreach ($productRecord->recipes as $recipe) {
                    $totalDeduction = $recipe->amount * $item['quantity'];

                    // ดึงล็อตทั้งหมดของวัตถุดิบนี้ เรียงเอาล็อตที่กำลังเปิดใช้ (in_use = 1) ขึ้นมาก่อน
                    // และเรียงตามลำดับ id เพื่อให้ตัดจากล็อตที่เก่าที่สุดก่อน
                    $lots = \App\Models\Real_ingrediant::where('ingredient_id', $recipe->ingredient_id)
                        ->orderByDesc('in_use')
                        ->orderBy('id')
                        ->get();

                    foreach ($lots as $lot) {
                        // ถ้าตัดครบตามจำนวนที่ต้องการแล้ว ให้ออกจากลูปของวัตถุดิบตัวนี้
                        if ($totalDeduction <= 0) break;

                        $lotRemaining = $lot->remaining();

                        // ถ้าล็อตนี้ไม่มีของแล้ว (อาจจะติดมาจากจังหวะก่อนหน้า) ให้ข้ามและลบทิ้งพร้อมเก็บ Log
                        if ($lotRemaining <= 0) {
                            \App\Models\InventoryLog::create([
                                'real_ingredient_id' => $lot->id,
                                'user_id' => Auth::id(),
                                'action' => 'out',
                                'quantity' => 0,
                                'reason' => "วัตถุดิบหมด (เคลียร์ล็อตที่ตกค้าง)",
                                'created_at' => now()
                            ]);
                            $lot->delete();
                            continue;
                        }

                        // คำนวณว่าจะตัดจากล็อตนี้เท่าไหร่ (เลือกระหว่าง "จำนวนที่ต้องตัด" หรือ "ของที่ล็อตนี้เหลืออยู่")
                        $deductAmount = min($totalDeduction, $lotRemaining);

                        // ถ้าล็อตนี้ดึงมาจากคลัง (in_use = 0) ให้เปลี่ยนสถานะเป็นเปิดใช้ (in_use = 1)
                        if ($lot->in_use == 0) {
                            $lot->in_use = 1;
                            $lot->save();
                        }

                        // สร้าง Log การตัดสต็อก ซึ่งจะส่งผลให้ remaining() ลดลงอัตโนมัติ
                        \App\Models\InventoryLog::create([
                            'real_ingredient_id' => $lot->id,
                            'user_id' => Auth::id(),
                            'action' => 'reduce',
                            'quantity' => $deductAmount,
                            'reason' => "ขายสินค้า: {$productRecord->name} x{$item['quantity']} (Order #{$sale->id})",
                            'created_at' => now()
                        ]);

                        // ลบยอดรวมที่ต้องตัดออกตามจำนวนที่เพิ่งตัดไป
                        $totalDeduction -= $deductAmount;

                        // รีเช็คสต็อกของล็อตนี้อีกรอบ ถ้าตัดแล้วหมดพอดี ให้ Soft Delete ทิ้งพร้อมเก็บ Log out
                        if ($lot->remaining() <= 0) {
                            \App\Models\InventoryLog::create([
                                'real_ingredient_id' => $lot->id,
                                'user_id' => Auth::id(),
                                'action' => 'out',
                                'quantity' => 0,
                                'reason' => "วัตถุดิบถูกใช้จนหมดหลังจากการขาย Order #{$sale->id}",
                                'created_at' => now()
                            ]);
                            $lot->delete();
                        }
                    }
                }
            }

            // ล้างตะกร้าเมื่อทำรายการสำเร็จ
            session()->forget('cart');
            return back()->with('success', 'สั่งซื้อสำเร็จและตัดสต็อกวัตถุดิบเรียบร้อยแล้ว');
        });
    }

    /**
     * ฟังก์ชันช่วยตรวจสอบทั้ง is_active และ is_show
     */
    public function check_active_product_in_cart($product, $cart)
    {
        // ถ้าสินค้าไม่ Active (สต็อกหมด) หรือ ไม่ Show (Admin ปิดการแสดงผล)
        if (!$product->is_active || !$product->is_show) {
            if (isset($cart[$product->id])) {
                unset($cart[$product->id]);
                session()->put('cart', $cart);
            }

            $reason = !$product->is_show ? "ถูกซ่อนโดยผู้ดูแล" : "วัตถุดิบไม่พอ";

            return response()->json([
                'status' => 'error',
                'message' => "สินค้า '{$product->name}' ไม่พร้อมจำหน่ายเนื่องจาก{$reason}",
                'cart' => $cart,
                'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
            ], 400);
        }
        return null;
    }
}