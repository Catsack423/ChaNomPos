<style>
    /* กรุณาย้ายไปfileccsถ้าจะใช้ */
    /* คุมโทนสีหลัก */
    :root {
        --primary-brown: #7B4A2E;
        --light-cream: #F5D7B2;
        --soft-bg: #FFF9F2;
        --danger-red: #E53E3E;
        --border-color: #E2E8F0;
    }

    /* Container สำหรับจัดการความกว้างและ Scroll บนมือถือ */
    .table-container {
        width: 100%;
        overflow-x: auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
    }

    .order-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 700px;
        /* กันตารางเบียดกันเกินไป */
    }

    /* Header */
    .order-table th {
        background-color: var(--light-cream);
        color: var(--primary-brown);
        padding: 16px;
        font-weight: 700;
        font-size: 14px;
        border-bottom: 2px solid rgba(123, 74, 46, 0.1);
    }

    /* Body Cells */
    .order-table td {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        color: #4A5568;
    }

    /* ไฮไลท์แถวเมื่อ Hover */
    .order-table tr:hover {
        background-color: rgba(245, 215, 178, 0.1);
    }

    /* ตกแต่งส่วนประกอบในตาราง */
    .item-tag {
        display: inline-block;
        background: #EDF2F7;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        margin: 2px;
    }

    .price-text {
        font-weight: 700;
        color: var(--primary-brown);
    }

    .staff-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .staff-icon {
        background: var(--light-cream);
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 12px;
    }

    /* ปุ่มลบ */
    .btn-delete {
        background: #fff;
        color: var(--danger-red);
        border: 1px solid var(--danger-red);
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-delete:hover {
        background: var(--danger-red);
        color: #fff;
    }

    /* กรุณาย้ายไป file ccsถ้าจะใช้ */
</style>
<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/orderhistory.css') }}">
    <x-tagbar />
    <div class="grid productcols">
        <div class="card">
            <div class="row">
                <h2 style="margin:0;">ประวัติการสั่งซื้อ</h2>
                <div class="spacer"></div>
            </div>
            {{-- <div class="mini" style="margin-top:6px; color:red;">

                loop จาก database order table มาลง
            </div> --}}
            <div class="search">
                <b>ค้นหา:</b>
                <input type="text">
                <input type="date">
            </div>
            <div class="table-container">
                <table class="order-table">
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
                        <tr>
                            <td>#001</td>
                            <td>
                                <span class="item-tag">ชานมx1</span>
                                <span class="item-tag">ชาเขียวนมx1</span>
                                <span class="item-tag">ไข่มุกx1</span>
                            </td>
                            <td>3 ชิ้น</td>
                            <td class="price-text">95 ฿</td>
    
                            <td>1 ม.ค. 2569</td>
                            
                        </tr>

                        <tr>
                            <td>#002</td>
                            <td>
                                <span class="item-tag">ชานมx1</span>
                                <span class="item-tag">ไข่มุกx1</span>
                            </td>
                            <td>2 ชิ้น</td>
                            <td class="price-text">95 ฿</td>
                            <td>10 ม.ค. 2569</td>
                            
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
