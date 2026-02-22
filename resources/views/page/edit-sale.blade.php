<x-app-layout>
    <div class="admin-container">
        <h2>แก้ไขคำสั่งซื้อ #{{ $sale->id }}</h2>

        <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div>
                <label>ยอดรวม</label>
                <input type="text" name="total_price" value="{{ $sale->total_price }}">
            </div>

            <button type="submit">บันทึก</button>
        </form>
    </div>
</x-app-layout>
