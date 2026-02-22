
<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/orderhistory.css') }}">
    <x-tagbar />
    <div class="grid productcols">
    <div class="card">

        <div class="header-section">
            <h2>ประวัติการสั่งซื้อ</h2>
            <div class="search">
                <input type="text" id="searchInput" placeholder="ค้นหา...">
            </div>
        </div>

        <div class="summary-container">
            <div class="summary-card">
                <div class="summary-label">วันที่</div>
                <div class="summary-value">
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-label">จำนวนออเดอร์</div>
                <div class="summary-value">
                    {{ $sales->total() }} รายการ
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-label">ยอดขายรวม</div>
                <div class="summary-value summary-highlight">
                    {{ number_format($totalSalesAmount, 2) }} ฿
                </div>
            </div>
        </div>


        <div class="table-container">
            <table id="order-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>สินค้าที่สั่ง</th>
                        <th>จำนวนทั้งหมด</th>
                        <th>ยอดรวม</th>
                        <th>วันที่</th>
                    </tr>
                </thead>

                <tbody>
                @if($sales->isEmpty())
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px;">
                            วันนี้ยังไม่มีรายการสั่งซื้อ
                        </td>
                    </tr>
                @else
                    @foreach($sales as $sale)
                        <tr>
                            <td class="order-id">
                                #{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                @foreach($sale->items as $item)
                                    <span class="item-tag">
                                        {{ $item->product->name }} x{{ $item->quantity }}
                                    </span>
                                @endforeach
                            </td>

                            <td class="qty-text">
                                {{ $sale->items->sum('quantity') }} ชิ้น
                            </td>

                            <td class="price-text">
                                {{ number_format($sale->total_price, 2) }} ฿
                            </td>

                            <td class="date-text">
                                {{ \Carbon\Carbon::parse($sale->sold_at)->format('d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            {{ $sales->links() }}
        </div>

    </div>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let input = this.value.toLowerCase();
    let rows = document.querySelectorAll("#order-table tbody tr");

    rows.forEach(function(row) {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
});
</script>
</x-app-layout>
