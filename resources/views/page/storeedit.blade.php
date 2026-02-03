<x-app-layout>

    <link rel="stylesheet" href="{{ asset('css/storeedit.css') }}">
    <x-tagbar />
    
       
        <div class="grid productcols" >
             <div class="card">
                <div class="row">
                    <h2 style="margin:0;">สต็อกวัตถุดิบ</h2>
                    <div class="spacer"></div>
                </div>
                <div class="mini" style="margin-top:6px; color:red;">
                    * ตัดสต็อกเมื่อ “รับออเดอร์” ตามสูตรสินค้า (Recipe)
                    loop จาก database มาลง ใช้หน่วยเป็นunit
                </div>
                <div id="stockTableWrap" style="margin-top:12px;"></div>
            </div>
            <div class="grid" style="gap:16px; max-width: 400px;">
                <div class="card">
                    <div class="row">
                        <h2 style="margin:0;">สถานะร้าน</h2>
                        <div class="spacer"></div>
                        <span id="shopBadgeStaff" class="badge open"><span
                                class="dot"></span><span>ร้านเปิด</span></span>
                    </div>
                    <div class="hint" style="margin-top:10px;">
                        เปิด–ปิดร้านส่งผลให้ User สั่งได้/สั่งไม่ได้ทันที (จำลอง)
                    </div>
                    <div class="row" style="margin-top:12px;">
                        <button id="toggleShopBtn" class="btn primary">สลับเปิด/ปิดร้าน</button>
                        <input id="closedReason" class="input" placeholder="เหตุผลตอนปิดร้าน (ถ้ามี)" />
                    </div>
                </div>
            </div>
            
        
</x-app-layout>
