<x-app-layout>
<link rel="stylesheet" href="{{ asset('css/bubble.css') }}">
<x-tagbar />
<main>
    
<div class="grid cols-2">

{{-- ================= MENU ================= --}}
<div class="card">

    <div class="row">
        <h2>เมนูชานม</h2>
        <div class="spacer"></div>
        <span class="badge open">
            <span class="dot"></span>
            ร้านเปิด
        </span>
    </div>

    {{-- Search --}}
    <div class="row" style="margin:15px 0;">
        <input id="searchMenu" class="input"
            placeholder="ค้นหาเมนู เช่น ชานม, โกโก้, ชาเขียว...">
    </div>

    {{-- Category Filter จาก DB --}}
    <div class="row" style="margin-bottom:15px;">
        <select id="filterType" class="input" style="max-width:220px;">
            <option value="all">ทุกประเภท</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Product List --}}
    <div id="productList" class="products">
        @foreach($products as $product)
        <div class="product"
             data-name="{{ strtolower($product->name) }}"
             data-type="{{ $product->category_id }}">

            <div class="thumb">
                {{ mb_substr($product->name,0,1) }}
            </div>

            <div class="name">{{ $product->name }}</div>

            <div class="row">
                <div class="price">
                    {{ number_format($product->price) }} ฿
                </div>
                <div class="spacer"></div>

                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="btn primary">เพิ่ม</button>
                </form>
            </div>

            <div class="mini">
                {{ $product->description }}
            </div>
        </div>
        @endforeach
    </div>
</div>


{{-- ================= CART ================= --}}
<div class="card">

    <div class="row">
        <h2>ตะกร้าของฉัน</h2>
        <div class="spacer"></div>
        <span class="pill">
            รวม:
            {{ number_format(collect($cart)->sum(fn($i)=>$i['price']*$i['quantity'])) }}
            ฿
        </span>
    </div>

    <div style="margin-top:15px;">

        @if(empty($cart))
            <div class="mini">ตะกร้าว่าง</div>
        @else

            @foreach($cart as $id => $item)
            <div class="card" style="padding:15px;margin-bottom:12px;">

                <div class="row">
                    <strong>{{ $item['name'] }}</strong>
                    <div class="spacer"></div>
                    <span class="pill">
                        {{ number_format($item['price']) }} ฿
                    </span>
                </div>

                <div class="row" style="margin-top:12px;gap:10px;align-items:center;">

                    {{-- ลด --}}
                    <form action="{{ route('cart.decrease') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $id }}">
                        <button class="btn">-</button>
                    </form>

                    <div class="pill">
                        จำนวน: {{ $item['quantity'] }}
                    </div>

                    {{-- เพิ่ม --}}
                    <form action="{{ route('cart.increase') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $id }}">
                        <button class="btn">+</button>
                    </form>

                    <div class="spacer"></div>

                    {{-- ลบ --}}
                    <form action="{{ route('cart.remove') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $id }}">
                        <button class="btn" style="background:#ef4444;color:white;">
                            ลบ
                        </button>
                    </form>

                </div>
            </div>
            @endforeach

        @endif
    </div>

    <form action="{{ route('checkout') }}" method="POST">
        @csrf
        <button class="btn primary"
            style="width:100%;margin-top:15px;"
            {{ empty($cart) ? 'disabled' : '' }}>
            ยืนยันสั่งซื้อ
        </button>
    </form>

    <div class="mini" style="margin-top:10px;">
        * การสั่งซื้อจะถูกส่งไปให้พนักงาน “รับออเดอร์”
        และตัดสต็อกเมื่อรับแล้ว
    </div>
</div>


{{-- ================= FILTER SCRIPT ================= --}}
<script>
const searchInput = document.getElementById('searchMenu');
const filterType = document.getElementById('filterType');
const products = document.querySelectorAll('.product');

function applyFilter(){
    let keyword = searchInput.value.toLowerCase();
    let type = filterType.value;

    products.forEach(p=>{
        let name = p.dataset.name;
        let ptype = p.dataset.type;

        let matchName = name.includes(keyword);
        let matchType = (type === "all" || ptype === type);

        p.style.display = (matchName && matchType)
            ? "block"
            : "none";
    });
}

searchInput.addEventListener('keyup', applyFilter);
filterType.addEventListener('change', applyFilter);
</script>

</x-app-layout>
