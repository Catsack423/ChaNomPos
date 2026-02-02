<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <x-tagbar />
    <div>
        <div class="grid productcols" >
            <div class="card" >
                <div class="row">
                    <h2 style="margin:0;">เมนูชานม</h2>
                    <div class="spacer"></div>
                    <span id="shopBadgeUser" class="badge open"><span class="dot"></span><span>ร้านเปิด</span></span>
                </div>

                <div class="row" style="margin:12px 0 10px;">
                    <input id="searchMenu" class="input" placeholder="ค้นหาเมนู เช่น ชานม, โกโก้, ชาเขียว..." />
                    <select id="filterType" style="max-width:220px;">
                        <option value="all">ทุกประเภท</option>
                        <option value="tea">ชา</option>
                        <option value="milk">นม</option>
                        <option value="topping">ท็อปปิ้ง</option>
                    </select>
                </div>

                <div id="productList" class="products">
                    <div class="product">
                        <div class="thumb">ชา</div>
                        <div class="name">ชาเขียวนม</div>
                        <div class="row">
                            <div class="price">50 ฿</div>
                            <div class="spacer"></div>
                            <button class="btn primary" data-add="2">เพิ่ม</button>
                        </div>
                        <div class="mini">สูตร: ชา 200ml, นม 60ml, น้ำตาล 18g, ไข่มุก 0 scoop</div>
                    </div>
                    <div class="product">
                        <div class="thumb">ชา</div>
                        <div class="name">แก้ให้มันloopจาก database</div>
                        <div class="row">
                            <div class="price">50 ฿</div>
                            <div class="spacer"></div>
                            <button class="btn primary" data-add="2">เพิ่ม</button>
                        </div>
                        <div class="mini">สูตร: ชา 200ml, นม 60ml, น้ำตาล 18g, ไข่มุก 0 scoop</div>
                    </div>
                </div>
            </div>

            <div class="grid" style="gap:16px;">
                <div class="card">
                    <div class="row">
                        <h2 style="margin:0;">ตะกร้าของฉัน</h2>
                        <div class="spacer"></div>
                        <span class="pill">รวม: <span id="cartTotal">0</span> ฿</span>
                    </div>
                    <div id="cartItems" class="grid" style="margin-top:12px; gap:10px;"></div>
                    <div class="row" style="margin-top:12px;">
                        <button id="checkoutBtn" class="btn primary" style="width:100%;">ยืนยันสั่งซื้อ</button>
                    </div>
                    <div class="mini" style="margin-top:10px;">
                        * การสั่งซื้อจะถูกส่งไปให้พนักงาน “รับออเดอร์” และตัดสต็อกเมื่อรับแล้ว
                    </div>
                </div>

                
        </div>
    </div>
</x-app-layout>
