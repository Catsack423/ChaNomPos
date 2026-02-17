<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', 1)->get();
        $categories = Category::all();
        $cart = session()->get('cart', []);
        // ==== เช็ค active product ใน cart ====
        if (!empty($cart)) {

            $ids = array_keys($cart);

            $activeIds = Product::whereIn('id', $ids)
                ->where('is_active', 1)
                ->pluck('id')
                ->toArray();

            // กรอง cart ให้เหลือเฉพาะ active
            $cart = array_filter($cart, function ($itemId) use ($activeIds) {
                return in_array($itemId, $activeIds);
            }, ARRAY_FILTER_USE_KEY);

            // อัพเดท session cart ใหม่
            session()->put('cart', $cart);
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
        $id = $request->product_id;

        if ($res = $this->check_active_product_in_cart($product, $cart)) {
            return $res;
        }

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
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
        $is_ok = true;
        $fasleProdcuct =[];
        $cart = session()->get('cart');
        if (!$cart) return back();

        foreach ($cart as $id => $item) {
            $product = Product::findOrFail($id);
            if (!$product || !$product->is_active) {
                unset($cart[$id]);
                $is_ok = false;
                array_push($fasleProdcuct,$product->name);
            }
        }
        session()->put('cart', $cart);

        if (!$is_ok) {
            return back()->with('error',  implode(', ', $fasleProdcuct).' is not activate');
        }


        if (empty($cart)) {
           return back()->with('error', 'ไม่มีสินค้าที่สั่งซื้อได้');
        }


        $total = collect($cart)
            ->sum(fn($item) => $item['price'] * $item['quantity']);

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
        }

        session()->forget('cart');

        return back()->with('success', 'สั่งซื้อสำเร็จ');
    }

    public function check_active_product_in_cart($product, $cart)
    {

        if (!$product->is_active) {
            if (!isset($cart[$product->id])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product is not active'
                ], 400);
            }

            unset($cart[$product->id]);
            session()->put('cart', $cart);
            $response = $this->responseCart($cart)->getData(true);
            $response['status'] = 'error';
            $response['message'] = 'Product in cart is not activate';
            return response()->json($response, 400);
        }
        return null;
    }
}
