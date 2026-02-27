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

        foreach ($product->recipes as $recipe) {
            $inventory = $recipe->ingredient->inventory;
            $neededAmount = $recipe->amount * $quantityRequested;

            if (!$inventory || $inventory->quantity < $neededAmount) {
                return false;
            }
        }
        return true;
    }

    public function index()
    {
        // 1. ตรวจสอบ Inventory และอัปเดต is_active ของ Product ทุกตัว
        $allProducts = Product::with('recipes.ingredient.inventory')->get();

        foreach ($allProducts as $product) {
            // เช็คว่าวัตถุดิบพอสำหรับทำอย่างน้อย 1 ชิ้นไหม
            $canMake = $this->hasEnoughStock($product->id, 1);

            if ($product->is_active != $canMake) {
                $product->update(['is_active' => $canMake ? 1 : 0]);
            }
        }

        // 2. ดึงข้อมูลที่อัปเดตแล้วไปแสดงผล
        $products = Product::where('is_active', 1)->get();
        $categories = Category::all();
        $cart = session()->get('cart', []);

        // 3. ตรวจสอบสินค้าในตะกร้า: ถ้าวัตถุดิบไม่พอสำหรับจำนวนที่สั่ง หรือสินค้าถูกปิดใช้งาน ให้เอาออก
        if (!empty($cart)) {
            $cartChanged = false;
            foreach ($cart as $id => $item) {
                if (!$this->hasEnoughStock($id, $item['quantity'])) {
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

    private function responseCart($cart)
    {
        return response()->json([
            'cart' => $cart,
            'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
        ]);
    }

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        $currentQty = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
        $newQty = $currentQty + 1;

        // ตรวจสอบสต็อกก่อนเพิ่ม
        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json(['status' => 'error', 'message' => 'วัตถุดิบไม่เพียงพอสำหรับจำนวนนี้'], 400);
        }

        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
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
        return $this->responseCart($cart);
    }

    public function increase(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        $newQty = $cart[$product->id]['quantity'] + 1;

        // ตรวจสอบสต็อกก่อนเพิ่มจำนวน
        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถเพิ่มจำนวนได้เนื่องจากวัตถุดิบไม่พอ'], 400);
        }

        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart);
    }

    public function decrease(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']--;
            if ($cart[$product->id]['quantity'] <= 0) {
                unset($cart[$product->id]);
            }
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart);
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart);
    }

    public function checkout()
    {
        $cart = session()->get('cart');
        if (!$cart) return back()->with('error', 'ไม่มีสินค้าในตะกร้า');

        // ใช้ Transaction เพื่อความปลอดภัยของข้อมูลสต็อกและยอดขาย
        return DB::transaction(function () use ($cart) {
            foreach ($cart as $id => $item) {
                $product = Product::find($id);
                
                // ตรวจสอบสต็อกด่านสุดท้ายก่อนตัดเงิน
                if (!$product || !$product->is_active || !$this->hasEnoughStock($id, $item['quantity'])) {
                    return back()->with('error', "สินค้า {$item['name']} วัตถุดิบไม่พอหรือไม่ได้เปิดใช้งานแล้ว");
                }
            }

            $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            // 1. บันทึกยอดขาย
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'total_price' => $total,
                'sold_at' => now()
            ]);

            foreach ($cart as $id => $item) {
                // 2. บันทึก SaleItem
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // 3. ตัดสต็อก Inventory และเขียน Log ตามสูตรอาหาร (Recipe)
                $product = Product::with('recipes')->find($id);
                foreach ($product->recipes as $recipe) {
                    $totalDeduction = $recipe->amount * $item['quantity'];

                    // ลดจำนวนในตาราง inventories
                    $inventory = Inventory::where('ingredient_id', $recipe->ingredient_id)->first();
                    $inventory->decrement('quantity', $totalDeduction);

                    // เขียน Log การใช้งานวัตถุดิบ
                    InventoryLog::create([
                        'ingredient_id' => $recipe->ingredient_id,
                        'user_id' => Auth::id(),
                        'action' => 'reduce',
                        'quantity' => $totalDeduction,
                        'reason' => "Sold Product: {$product->name} x{$item['quantity']}",
                        'created_at' => now()
                    ]);
                }
            }

            session()->forget('cart');
            return back()->with('success', 'สั่งซื้อสำเร็จและอัปเดตสต็อกเรียบร้อย');
        });
    }

    public function check_active_product_in_cart($product, $cart)
    {
        // ตรวจสอบเบื้องต้นว่าสินค้ายัง Active อยู่หรือไม่
        if (!$product->is_active) {
            if (isset($cart[$product->id])) {
                unset($cart[$product->id]);
                session()->put('cart', $cart);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'สินค้านี้ไม่พร้อมจำหน่ายในขณะนี้',
                'cart' => $cart,
                'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
            ], 400);
        }
        return null;
    }
}