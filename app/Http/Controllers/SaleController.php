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

        return view('page.dashboard', compact('products', 'cart', 'categories'));
    }

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

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
        return back();
    }

    public function increase(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
            session()->put('cart', $cart);
        }

        return back();
    }

    public function decrease(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity']--;

            if ($cart[$id]['quantity'] <= 0) {
                unset($cart[$id]);
            }

            session()->put('cart', $cart);
        }

        return back();
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return back();
    }

    public function checkout()
    {
        $cart = session()->get('cart');
        if (!$cart) return back();

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
}
