<x-app-layout>
    <x-tagbaradmin />
    <link rel="stylesheet" href="{{ asset('css/adminhistory.css') }}">
    <div class="admin-container">

    <h2 class="admin-title">รายงานยอดขายทั้งหมด</h2>
    <div class="admin-filter-card">
        <form method="GET" action="{{ route('admindashboard') }}" class="admin-filter-form">

            <div class="filter-group search">
                <label>ค้นหาออเดอร์</label>
                <input type="text" name="search"
                    placeholder="ค้นหา..."
                    value="{{ request('search') }}">
            </div>

            <div class="filter-group">
                <label>จากวันที่</label>
                <input type="date" name="from_date"
                    value="{{ request('from_date') }}">
            </div>

            <div class="filter-group">
                <label>ถึงวันที่</label>
                <input type="date" name="to_date"
                    value="{{ request('to_date') }}">
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn-search">
                    ค้นหา
                </button>

                <a href="{{ route('admindashboard') }}" class="btn-reset">
                    รีเซ็ต
                </a>
            </div>

        </form>
    </div>

    <div class="grand-total-box">
    <div class="grand-total-label">
        ยอดขายรวมทั้งหมด
    </div>
    <div class="grand-total-price">
        {{ number_format($grandTotal, 2) }} ฿
    </div>
</div>

    @foreach($salesByMonth as $month => $monthlySales)
            @php
                $monthlyTotal = $monthlySales->sum('total_price');
            @endphp

            <div class="month-header">
                <div>
                    {{ $month }}
                </div>
                <div class="month-total">
                    ยอดขายรวม {{ number_format($monthlyTotal, 2) }} ฿
                </div>
            </div>

            <div class="table-container">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ยอดรวม</th>
                            <th>วันที่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySales as $sale)
                            <tr>
                                <td>#{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</td>

                                <td>
                                    @foreach($sale->items as $item)
                                        <span class="item-tag">
                                            {{ $item->product->name }} x{{ $item->quantity }}
                                        </span>
                                    @endforeach
                                </td>

                                <td>
                                    {{ $sale->items->sum('quantity') }} ชิ้น
                                </td>

                                <td class="price-text">
                                    {{ number_format($sale->total_price, 2) }} ฿
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($sale->sold_at)->format('d/m/Y') }}
                                </td>

                                <td>
                                    <div style="display:flex; gap:8px;">

                                        <button 
                                            class="btn-edit"
                                            onclick='openEditModal(@json($sale))'>
                                            แก้ไข
                                        </button>

                                        <form action="{{ route('admin.sales.destroy', $sale->id) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('ยืนยันการลบรายการนี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">
                                                ลบ
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
</div>

<div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal-box large">

        <div class="modal-header">
            <h3>แก้ไขคำสั่งซื้อ</h3>
            <span onclick="closeEditModal()" class="close-btn">✕</span>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div id="itemsContainer"></div>

            <button type="button" onclick="addItem()">+ เพิ่มสินค้า</button><br>

            <label>ราคารวม</label>
            <input type="text" id="edit_price" readonly>

            <label>วันที่และเวลา</label>
            <input type="datetime-local" 
                   step="1"
                   name="sold_at" 
                   id="edit_date">

            <button type="submit" class="btn-save">บันทึก</button>
        </form>

    </div>
</div>

<script>
let allProducts = @json(\App\Models\Product::all());

function openEditModal(sale) {

    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('editForm').action = `/admin/sales/${sale.id}`;

    let date = new Date(sale.sold_at);
    let formatted = date.toISOString().slice(0,19);
    document.getElementById('edit_date').value = formatted;

    let container = document.getElementById('itemsContainer');
    container.innerHTML = '';

    sale.items.forEach(item => {
        addItem(item.product_id, item.quantity);
    });

    calculateTotal();
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function addItem(productId = '', quantity = 1) {

    let container = document.getElementById('itemsContainer');

    let options = allProducts.map(p => 
        `<option value="${p.id}" ${p.id == productId ? 'selected' : ''}>
            ${p.name} (${p.price} ฿)
        </option>`
    ).join('');

    container.innerHTML += `
        <div class="item-row" style="display:flex; gap:10px; margin-bottom:10px;">
            <select name="products[]" onchange="calculateTotal()">
                ${options}
            </select>

            <input type="number" name="quantities[]" 
                   value="${quantity}" min="1"
                   oninput="calculateTotal()">

            <button type="button"
                    onclick="this.parentElement.remove(); calculateTotal();">
                ลบ
            </button>
        </div>
    `;
}

function calculateTotal() {

    let total = 0;

    document.querySelectorAll('.item-row').forEach(row => {

        let productId = row.querySelector('select').value;
        let quantity = row.querySelector('input').value;

        let product = allProducts.find(p => p.id == productId);

        if (product) {
            total += product.price * quantity;
        }
    });

    document.getElementById('edit_price').value = total.toFixed(2) + " ฿";
}

window.onclick = function(event) {
    let modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
}
</script>

</x-app-layout>
