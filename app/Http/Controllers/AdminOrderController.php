<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Sale;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    public function index(Request $request)
{
    $query = Sale::with('items.product');
        if ($request->filled('search')) {
    $rawSearch = trim($request->search);
    $cleanSearch = str_replace('#', '', $rawSearch);
    $query->where(function ($q) use ($cleanSearch) {
        $q->orWhereRaw("CAST(id AS CHAR) LIKE ?", ['%' . $cleanSearch . '%']);
        $q->orWhereRaw("LPAD(id, 4, '0') LIKE ?", ['%' . $cleanSearch . '%']);
        $q->orWhereHas('items.product', function ($subQuery) use ($cleanSearch) {
            $subQuery->where('name', 'like', '%' . $cleanSearch . '%');
        });

    });
}

    if ($request->filled('from_date')) {
        $query->whereDate('sold_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('sold_at', '<=', $request->to_date);
    }

    $sales = $query->orderBy('sold_at', 'desc')->get();
    $grandTotal = $sales->sum(function ($sale) {
        return $sale->items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    });

    $salesByMonth = $sales->groupBy(function ($sale) {
        return \Carbon\Carbon::parse($sale->sold_at)->format('F Y');
    });

    return view('page.admindashboard', compact(
        'salesByMonth',
        'grandTotal'
    ));
}

    public function edit($id) {
        $sale = Sale::with('items.product')->findOrFail($id);
        return view('page.edit-sale', compact('sale'));
    }

    public function destroy($id) {
        $sale = Sale::findOrFail($id);
        $sale->items()->delete();
        $sale->delete();

        return redirect()->back()->with('success', 'ลบสำเร็จแล้ว');
    }

    public function update(Request $request, $id) {
        $sale = Sale::findOrFail($id);
        $sale->items()->delete();
        $total = 0;

        if ($request->has('products')) {

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
        }

        $sale->update([
            'total_price' => $total,
            'sold_at'     => $request->sold_at,
        ]);

        return redirect()->back()->with('success', 'แก้ไขเรียบร้อย');
    }
}
