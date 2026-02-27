<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
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
        $product = Product::with('recipes.ingredient.inventory')->find($productId);
        if (!$product) return false;

        // ถ้าสินค้าไม่มีสูตรอาหาร ให้ถือว่าขายไม่ได้
        if ($product->recipes->isEmpty()) return false;

        foreach ($product->recipes as $recipe) {
            // ตรวจสอบความสมบูรณ์ของข้อมูลวัตถุดิบและคลังสินค้า
            if (!$recipe->ingredient || !$recipe->ingredient->inventory) {
                return false; 
            }

            $inventory = $recipe->ingredient->inventory;
            $neededAmount = $recipe->amount * $quantityRequested;

            if ($inventory->quantity < $neededAmount) {
                return false;
            }
        }
        return true;
    }

    /**
     * หน้าแรก Dashboard พร้อมระบบ Auto-Disable สินค้าที่ของหมด
     */
    public function index()
    {
        // 1. ตรวจสอบ Inventory และอัปเดตสถานะ Active เฉพาะตัวที่ของหมด
        $allProducts = Product::with('recipes.ingredient.inventory')->get();

        foreach ($allProducts as $product) {
            $canMake = $this->hasEnoughStock($product->id, 1);

            // เงื่อนไข: ถ้าของไม่พอและยังเปิดอยู่ให้ปิด แต่ถ้าของพอจะไม่สั่งเปิดเอง (ให้ Admin เปิดมือ)
            if (!$canMake && $product->is_active == 1) {
                $product->update(['is_active' => 0]);
            }
        }

        // 2. ดึงข้อมูลสินค้าที่ Active อยู่มาแสดงผล
        $products = Product::where('is_active', 1)->get();
        $categories = Category::all();
        $cart = session()->get('cart', []);

        // 3. กรองสินค้าในตะกร้าที่อาจจะถูกปิดไปแล้วหรือสต็อกไม่พอ
        if (!empty($cart)) {
            $cartChanged = false;
            foreach ($cart as $id => $item) {
                $productInCart = Product::find($id);
                if (!$productInCart || !$productInCart->is_active || !$this->hasEnoughStock($id, $item['quantity'])) {
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

    /**
     * ปรับปรุง JSON Response ให้ส่งสถานะและข้อความกลับ
     */
    private function responseCart($cart, $message = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'cart' => $cart,
            'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
        ]);
    }

    /**
     * เพิ่มสินค้าลงตะกร้า (เช็ค Active และ Stock)
     */
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        // ตรวจสอบว่าสินค้ายังเปิดขายอยู่หรือไม่
        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        $currentQty = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
        $newQty = $currentQty + 1;

        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json(['status' => 'error', 'message' => 'วัตถุดิบไม่เพียงพอสำหรับจำนวนนี้'], 400);
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
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

    /**
     * เพิ่มจำนวนสินค้าในตะกร้า (เช็ค Active และ Stock)
     */
    public function increase(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        // ตรวจสอบว่าสินค้ายยัง Active หรือไม่ก่อนเพิ่มจำนวน
        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        $newQty = ($cart[$product->id]['quantity'] ?? 0) + 1;

        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถเพิ่มได้เนื่องจากวัตถุดิบไม่พอ'], 400);
        }

        $cart[$product->id]['quantity']++;
        session()->put('cart', $cart);
        return $this->responseCart($cart, "เพิ่มจำนวน '{$product->name}' เรียบร้อย");
    }

    /**
     * ลดจำนวนสินค้าในตะกร้า
     */
    public function decrease(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']--;
            if ($cart[$product->id]['quantity'] <= 0) {
                unset($cart[$product->id]);
            }
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart, "ลดจำนวนสินค้าเรียบร้อย");
    }

    /**
     * นำสินค้าออกจากตะกร้า
     */
    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart, "ลบสินค้าออกจากตะกร้าแล้ว");
    }

    /**
     * ขั้นตอนชำระเงิน ตรวจสอบสถานะ Active และตัดสต็อกพร้อมบันทึก Log
     */
    public function checkout()
    {
        $cart = session()->get('cart');
        if (!$cart) return back()->with('error', 'ไม่มีสินค้าในตะกร้า');

        return DB::transaction(function () use ($cart) {
            // ตรวจสอบสถานะสินค้าและสต็อกด่านสุดท้าย
            foreach ($cart as $id => $item) {
                $product = Product::find($id);
                if (!$product || !$product->is_active || !$this->hasEnoughStock($id, $item['quantity'])) {
                    // หากไม่ผ่าน ให้เตะสินค้านั้นออกจากตะกร้าทันที
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                    return back()->with('error', "สินค้า " . ($product->name ?? 'บางรายการ') . " ไม่พร้อมจำหน่ายหรือสต็อกไม่พอ");
                }
            }

            $total = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'total_price' => $total,
                'sold_at' => now()
            ]);

            foreach ($cart as $id => $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // ตัดสต็อกวัตถุดิบตามสูตร
                $productRecord = Product::with('recipes.ingredient')->find($id);
                foreach ($productRecord->recipes as $recipe) {
                    if (!$recipe->ingredient) continue;

                    $totalDeduction = $recipe->amount * $item['quantity'];
                    $inventory = Inventory::where('ingredient_id', $recipe->ingredient_id)->first();
                    
                    if ($inventory) {
                        $inventory->decrement('quantity', $totalDeduction);

                        // บันทึก Log การตัดสต็อก (Action: reduce)
                        InventoryLog::create([
                            'ingredient_id' => $recipe->ingredient_id,
                            'user_id' => Auth::id(),
                            'action' => 'reduce',
                            'quantity' => $totalDeduction,
                            'reason' => "ขายสินค้า: {$productRecord->name} x{$item['quantity']} (Order #{$sale->id})",
                            'created_at' => now()
                        ]);
                    }
                }
            }

            session()->forget('cart');
            return back()->with('success', 'สั่งซื้อสำเร็จและตัดสต็อกวัตถุดิบเรียบร้อยแล้ว');
        });
    }

    /**
     * ฟังก์ชันช่วยตรวจสอบสถานะ Active และจัดการตะกร้า
     */
    public function check_active_product_in_cart($product, $cart)
    {
        if (!$product->is_active) {
            if (isset($cart[$product->id])) {
                unset($cart[$product->id]);
                session()->put('cart', $cart);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => "สินค้า '{$product->name}' ถูกปิดการใช้งานหรือวัตถุดิบไม่พอ",
                'cart' => $cart,
                'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
            ], 400);
        }
        return null;
    }
}