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
            // ตรวจสอบว่ามีข้อมูล Ingredient และ Inventory อยู่จริงหรือไม่
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

    public function index()
    {
        // 1. ตรวจสอบ Inventory และอัปเดตสถานะ Active ของสินค้าทั้งหมด
        $allProducts = Product::with('recipes.ingredient.inventory')->get();

        foreach ($allProducts as $product) {
            $canMake = $this->hasEnoughStock($product->id, 1);

            if ($product->is_active != $canMake) {
                $product->update(['is_active' => $canMake ? 1 : 0]);
            }
        }

        // 2. ดึงข้อมูลแสดงผล
        $products = Product::where('is_active', 1)->get();
        $categories = Category::all();
        $cart = session()->get('cart', []);

        // 3. กรองสินค้าในตะกร้าที่ข้อมูลไม่สมบูรณ์หรือสต็อกหมด
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

    /**
     * ปรับปรุงให้ส่งค่า Status และ Message กลับไปด้วย
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

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        $currentQty = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
        $newQty = $currentQty + 1;

        // เช็คสต็อก
        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'วัตถุดิบไม่เพียงพอสำหรับจำนวนที่ต้องการ'
            ], 400);
        }

        // เช็คความพร้อมจำหน่าย
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
        return $this->responseCart($cart, "เพิ่ม '{$product->name}' ลงตะกร้าแล้ว");
    }

    public function increase(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        $newQty = ($cart[$product->id]['quantity'] ?? 0) + 1;

        // เช็คสต็อกก่อนเพิ่ม
        if (!$this->hasEnoughStock($product->id, $newQty)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'ไม่สามารถเพิ่มจำนวนได้เนื่องจากวัตถุดิบไม่พอ'
            ], 400);
        }

        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart, "เพิ่มจำนวน '{$product->name}' เรียบร้อย");
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
        return $this->responseCart($cart, "ลดจำนวน '{$product->name}' เรียบร้อย");
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;
        $name = isset($cart[$id]) ? $cart[$id]['name'] : 'สินค้า';

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        session()->put('cart', $cart);
        return $this->responseCart($cart, "นำ '{$name}' ออกจากตะกร้าแล้ว");
    }

    public function checkout()
    {
        $cart = session()->get('cart');
        if (!$cart) return back()->with('error', 'ไม่มีสินค้าในตะกร้า');

        return DB::transaction(function () use ($cart) {
            foreach ($cart as $id => $item) {
                if (!$this->hasEnoughStock($id, $item['quantity'])) {
                    return back()->with('error', "สินค้า {$item['name']} มีปัญหาด้านวัตถุดิบหรือสต็อกไม่พอในขณะนี้");
                }
            }

            $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

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

                $product = Product::with('recipes.ingredient')->find($id);
                foreach ($product->recipes as $recipe) {
                    if (!$recipe->ingredient) continue;

                    $totalDeduction = $recipe->amount * $item['quantity'];
                    $inventory = Inventory::where('ingredient_id', $recipe->ingredient_id)->first();
                    
                    if ($inventory) {
                        $inventory->decrement('quantity', $totalDeduction);

                        // บันทึก Log
                        InventoryLog::create([
                            'ingredient_id' => $recipe->ingredient_id,
                            'user_id' => Auth::id(),
                            'action' => 'reduce',
                            'quantity' => $totalDeduction,
                            'reason' => "ขายสินค้า: {$product->name} x{$item['quantity']} (Order #{$sale->id})",
                            'created_at' => now()
                        ]);
                    }
                }
            }

            session()->forget('cart');
            return back()->with('success', 'สั่งซื้อสำเร็จและตัดสต็อกวัตถุดิบเรียบร้อยแล้ว');
        });
    }

    public function check_active_product_in_cart($product, $cart)
    {
        if (!$product->is_active) {
            if (isset($cart[$product->id])) {
                unset($cart[$product->id]);
                session()->put('cart', $cart);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'สินค้านี้ไม่พร้อมจำหน่ายเนื่องจากข้อมูลวัตถุดิบไม่ครบถ้วนหรือหมดชั่วคราว',
                'cart' => $cart,
                'total' => collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])
            ], 400);
        }
        return null;
    }
}