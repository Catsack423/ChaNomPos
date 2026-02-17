<?php
namespace App\Http\Controllers;
use App\Models\Sale;
use Carbon\Carbon;

class OrderHistoryController extends Controller
{
public function index()
{
    $today = Carbon::today();

    $sales = Sale::with('items.product')
        ->whereDate('sold_at', $today)
        ->orderBy('sold_at', 'desc')
        ->paginate(5);


    $totalSalesAmount = Sale::whereDate('sold_at', $today)
        ->sum('total_price');

    return view('page.orderhistory', compact('sales', 'totalSalesAmount'));
}

}
