<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Sale::with('items.product');

            if ($request->filled('search')) {

                $search = trim(str_replace('#', '', $request->search));

                $query->where(function ($q) use ($search) {

                    $q->where('id', 'like', "%{$search}%")

                    ->orWhereHas('items.product', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%");
                    });

                });
            }

            if ($request->filled('from_date')) {
                $query->whereDate('sold_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('sold_at', '<=', $request->to_date);
            }

            $sales = $query->orderBy('sold_at', 'desc')
               ->orderBy('id', 'desc')
               ->get();

            $grandTotal = $sales->sum(function ($sale) {
                return $sale->items->sum(function ($item) {
                    return $item->price * $item->quantity;
                });
            });

            $salesByMonth = $sales->groupBy(function ($sale) {
                return Carbon::parse($sale->sold_at)->format('F Y');
            });

            return view('page.admindashboard', compact('salesByMonth', 'grandTotal'));

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $sale = Sale::with('items.product')->findOrFail($id);
            return view('page.edit-sale', compact('sale'));
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')->with('error', 'ไม่พบข้อมูลออเดอร์ที่ต้องการแก้ไข');
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $sale = Sale::findOrFail($id);
                $sale->items()->delete();
                $sale->delete();
            });

            return redirect()->back()->with('success', 'ลบรายการสั่งซื้อสำเร็จแล้ว');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ไม่สามารถลบรายการได้: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // 🔥 เช็คก่อนเข้า transaction
        if (!$request->has('products') || count($request->products) == 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'ออเดอร์ต้องมีสินค้าอย่างน้อย 1 รายการ');
        }

        try {
            DB::transaction(function () use ($request, $id) {

                $sale = Sale::findOrFail($id);

                $sale->items()->delete();

                $total = 0;

                foreach ($request->products as $index => $productId) {

                    $product = Product::findOrFail($productId);
                    $quantity = $request->quantities[$index];

                    $subtotal = $product->price * $quantity;
                    $total += $subtotal;

                    $sale->items()->create([
                        'product_id' => $productId,
                        'quantity'   => $quantity,
                        'price'      => $product->price,
                    ]);
                }

                $sale->update([
                    'total_price' => $total,
                    'sold_at'     => $request->sold_at,
                ]);
            });

            return redirect()->back()->with('success', 'อัปเดตสำเร็จ');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'ไม่สามารถอัปเดตข้อมูลได้');
        }
    }
}