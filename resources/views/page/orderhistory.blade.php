<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/orderhistory.css') }}">
    <x-tagbar />
    <div class="grid productcols" style="max-width: 80%; ">
        <div class="card">
            <div class="row">
                <h2 style="margin:0;">ประวัติการสั่งซื้อ</h2>
                <div class="spacer"></div>
            </div>
            <div class="mini" style="margin-top:6px; color:red;">
                
                loop จาก database order table  มาลง ใช้หน่วยเป็นunit
            </div>
            <div id="stockTableWrap" style="margin-top:12px;"></div>
        </div>
    </div>
</x-app-layout>
