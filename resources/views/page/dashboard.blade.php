<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/bubble.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    <input id="searchMenu" class="input" placeholder="ค้นหาเมนู เช่น ชานม, โกโก้, ชาเขียว...">
                </div>

                {{-- Category --}}
                <div class="row" style="margin-bottom:15px;">
                    <select id="filterType" class="input" style="max-width:220px;">
                        <option value="all">ทุกประเภท</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Products --}}
                <div id="productList" class="products">
                    @foreach ($products as $product)
                        <div class="product" data-name="{{ strtolower($product->name) }}"
                            data-type="{{ $product->category_id }}">

                            <div class="thumb">
                                {{ mb_substr($product->name, 0, 1) }}
                            </div>

                            <div class="name">{{ $product->name }}</div>

                            <div class="row">
                                <div class="price">
                                    {{ number_format($product->price) }} ฿
                                </div>
                                <div class="spacer"></div>

                                <button class="btn primary add-to-cart" data-id="{{ $product->id }}">
                                    เพิ่ม
                                </button>
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
                    <span class="pill" id="cartTotal">
                        รวม:
                        {{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity'])) }}
                        ฿
                    </span>
                </div>

                <div id="cartContainer" style="margin-top:15px;">

                    @if (empty($cart))
                        <div class="mini">ตะกร้าว่าง</div>
                    @else
                        @foreach ($cart as $id => $item)
                            <div class="card" style="padding:15px;margin-bottom:12px;">

                                <div class="row">
                                    <strong>{{ $item['name'] }}</strong>
                                    <div class="spacer"></div>
                                    <span class="pill">
                                        {{ number_format($item['price']) }} ฿
                                    </span>
                                </div>

                                <div class="row" style="margin-top:12px;gap:10px;align-items:center;">

                                    <button class="btn decrease" data-id="{{ $id }}">-</button>

                                    <div class="pill quantity-{{ $id }}">
                                        จำนวน: {{ $item['quantity'] }}
                                    </div>

                                    <button class="btn increase" data-id="{{ $id }}">+</button>

                                    <div class="spacer"></div>

                                    <button class="btn remove" data-id="{{ $id }}"
                                        style="background:#ef4444;color:white;">
                                        ลบ
                                    </button>

                                </div>
                            </div>
                        @endforeach

                    @endif
                </div>

                <form action="{{ route('checkout') }}" method="POST">
                    @csrf
                    <button class="btn primary" style="width:100%;margin-top:15px;" id="checkoutBtn"
                        {{ empty($cart) ? 'disabled' : '' }}>
                        ยืนยันสั่งซื้อ
                    </button>
                </form>

                <div class="mini" style="margin-top:10px;">
                    * การสั่งซื้อจะถูกส่งไปให้พนักงานรับออเดอร์
                </div>
            </div>

        </div>
    </main>

    {{-- error --}}
    @if (session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
    @endif

    {{-- ================= FILTER SCRIPT ================= --}}
    <script>
        const searchInput = document.getElementById('searchMenu');
        const filterType = document.getElementById('filterType');
        const products = document.querySelectorAll('.product');

        function applyFilter() {
            let keyword = searchInput.value.toLowerCase();
            let type = filterType.value;

            products.forEach(p => {
                let matchName = p.dataset.name.includes(keyword);
                let matchType = (type === "all" || p.dataset.type === type);
                p.style.display = (matchName && matchType) ? "block" : "none";
            });
        }

        searchInput.addEventListener('keyup', applyFilter);
        filterType.addEventListener('change', applyFilter);
    </script>


    {{-- ================= AJAX CART SCRIPT ================= --}}
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        function sendRequest(url, id) {
            fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrf
                    },
                    body: JSON.stringify({
                        product_id: id
                    })
                })
                .then(async res => {

                    let data = await res.json();

                    if (!res.ok) {
                        alert(data.message);

                        if (data.cart) {
                            updateCartUI(data.cart, data.total);
                        }
                        return;
                    }

                    updateCartUI(data.cart, data.total);
                })
                .catch(err => {
                    alert("Server error");
                });
        }

        function updateCartUI(cart, total) {
            //ปุ้มตะกร้า
            let checkoutBtn = document.getElementById("checkoutBtn");
            // อัปเดตยอดรวม
            document.getElementById('cartTotal').innerHTML =
                "รวม: " + new Intl.NumberFormat().format(total) + " ฿";

            const container = document.getElementById('cartContainer');
            container.innerHTML = '';

            if (Object.keys(cart).length === 0) {
                container.innerHTML = '<div class="mini">ตะกร้าว่าง</div>';
                return;
            }

            for (let id in cart) {

                let item = cart[id];

                container.innerHTML += `
        <div class="card" style="padding:15px;margin-bottom:12px;">
            <div class="row">
                <strong>${item.name}</strong>
                <div class="spacer"></div>
                <span class="pill">
                    ${new Intl.NumberFormat().format(item.price)} ฿
                </span>
            </div>

            <div class="row" style="margin-top:12px;gap:10px;align-items:center;">
                <button class="btn decrease" data-id="${id}">-</button>
                <div class="pill">จำนวน: ${item.quantity}</div>
                <button class="btn increase" data-id="${id}">+</button>
                <div class="spacer"></div>
                <button class="btn remove"
                    data-id="${id}"
                    style="background:#ef4444;color:white;">
                    ลบ
                </button>
            </div>
        </div>
        `;
            }
            if (Object.keys(cart).length === 0) {
                checkoutBtn.disabled = true;
            } else {
                checkoutBtn.disabled = false;
            }
            attachEvents();
        }

        function attachEvents() {

            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.onclick = () =>
                    sendRequest("{{ route('cart.add') }}", btn.dataset.id);
            });

            document.querySelectorAll('.increase').forEach(btn => {
                btn.onclick = () =>
                    sendRequest("{{ route('cart.increase') }}", btn.dataset.id);
            });

            document.querySelectorAll('.decrease').forEach(btn => {
                btn.onclick = () =>
                    sendRequest("{{ route('cart.decrease') }}", btn.dataset.id);
            });

            document.querySelectorAll('.remove').forEach(btn => {
                btn.onclick = () =>
                    sendRequest("{{ route('cart.remove') }}", btn.dataset.id);
            });
        }

        attachEvents();
    </script>

</x-app-layout>
