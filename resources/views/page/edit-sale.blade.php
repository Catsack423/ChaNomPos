<x-app-layout>
    <div class="admin-container">
        <h2>แก้ไขคำสั่งซื้อ #{{ $sale->id }}</h2>

        <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div>
                <label>วันที่ขาย</label>
                <input type="datetime-local" name="sold_at"
                    value="{{ \Carbon\Carbon::parse($sale->sold_at)->format('Y-m-d\TH:i') }}">
            </div>

            <hr>

            <h3>รายการสินค้า</h3>

            @foreach($sale->items as $item)
                <div style="margin-bottom:10px;">
                    
                    <!-- product id -->
                    <input type="hidden" name="products[]" value="{{ $item->product_id }}">

                    <label>{{ $item->product->name }}</label>

                    <!-- quantity -->
                    <input type="number"
                        name="quantities[]"
                        value="{{ $item->quantity }}"
                        min="1"
                        required>
                </div>
            @endforeach

            <br>

            <button type="submit">บันทึก</button>
        </form>
    </div>
</x-app-layout>
