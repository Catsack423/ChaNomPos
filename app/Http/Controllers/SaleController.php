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

        // แก้จุดนี้: ถ้าไม่มีสูตร ให้ถือว่า "มีของ" (Pass)
        if ($product->recipes->isEmpty()) {
            return true;
        }

        foreach ($product->recipes as $recipe) {
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
     * หน้าแรก Dashboard
     */
    public function index()
    {
        // 1. ตรวจสอบ Inventory และอัปเดตสถานะ is_active ตามสต็อก (is_show ไม่เกี่ยวกับการคำนวณสต็อก)
        $allProducts = Product::with('recipes.ingredient.inventory')->get();

        foreach ($allProducts as $product) {
            $canMake = $this->hasEnoughStock($product->id, 1);

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

                // ตรวจสอบ: ต้องมีสินค้า + ต้อง Active + ต้อง Show + สต็อกต้องพอตามจำนวน
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

    /**
     * เพิ่มสินค้าลงตะกร้า (ตรวจสอบทั้ง Active และ Show)
     */
    public function addToCart(Request $request)
    {
        $product = Product::find($request->product_id);
        $cart = session()->get('cart', []);

        if (!$product){
            return response()->json(['status' => 'error', 'message' => 'สินค้าไม่พร้อมใช้งาน'], 400);
        }

        // ตรวจสอบผ่านฟังก์ชันตัวช่วย (check_active_product_in_cart ตรวจทั้ง active และ show)
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
     * เพิ่มจำนวน (ตรวจสอบทั้ง Active และ Show)
     */
    public function increase(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

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
     * ชำระเงิน ตรวจสอบด่านสุดท้าย (ต้อง Active และ Show)
     */
    public function checkout()
    {
        $cart = session()->get('cart');
        if (!$cart) return back()->with('error', 'ไม่มีสินค้าในตะกร้า');

        return DB::transaction(function () use ($cart) {
            foreach ($cart as $id => $item) {
                $product = Product::find($id);
                // ตรวจสอบ: สินค้าต้องมี + ต้อง Active + ต้อง Show + สต็อกต้องพอ
                if (!$product || !$product->is_active || !$product->is_show || !$this->hasEnoughStock($id, $item['quantity'])) {
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                    return back()->with('error', "สินค้า " . ($product->name ?? 'บางรายการ') . " ไม่พร้อมจำหน่ายหรือถูกซ่อนโดยผู้ดูแล");
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

                $productRecord = Product::with('recipes.ingredient')->find($id);
                foreach ($productRecord->recipes as $recipe) {
                    if (!$recipe->ingredient) continue;

                    $totalDeduction = $recipe->amount * $item['quantity'];
                    $inventory = Inventory::where('ingredient_id', $recipe->ingredient_id)->first();

                    if ($inventory) {
                        $inventory->decrement('quantity', $totalDeduction);

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
