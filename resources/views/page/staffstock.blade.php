<x-app-layout>

    <link rel="stylesheet" href="{{ asset('css/staffstock.css') }}">
    <style>
        /* คุมโทนสีและค่าความมน (Global-like variables) */
        :root {
            --primary-brown: #7B4A2E;
            --secondary-cream: #F5D7B2;
            --bg-soft: #FFF9F2;
            --radius-lg: 18px;
            /* ความมนของการ์ด */
            --radius-sm: 12px;
            /* ความมนของปุ่ม/input */
            --shadow-soft: 0 8px 30px rgba(123, 74, 46, 0.08);
        }

        /* Container การ์ดแบบมนๆ */
        .card-container {
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(123, 74, 46, 0.1);
            overflow: hidden;
            margin: 20px 0;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--secondary-cream);
        }

        .title {
            margin: 0;
            color: var(--primary-brown);
            font-weight: 800;
        }

        /* ตารางแบบโค้งมน */
        .bubble-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bubble-table th {
            background: var(--bg-soft);
            color: var(--primary-brown);
            padding: 15px 20px;
            font-size: 14px;
            text-align: left;
        }

        .bubble-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f8f1eb;
            color: #555;
        }

        /* ตกแต่ง Badge และตัวเลข */
        .id-badge {
            background: var(--secondary-cream);
            color: var(--primary-brown);
            padding: 4px 10px;
            border-radius: 999px;
            /* มนกลม */
            font-weight: bold;
            font-size: 12px;
        }

        .qty-pill {
            background: #f0f0f0;
            padding: 6px 15px;
            border-radius: 999px;
            font-weight: 800;
            color: var(--primary-brown);
        }

        .qty-pill.empty {
            background: #FFE5E5;
            color: #D63031;
        }

        /* ส่วนจัดการสต็อก (Input + Buttons) */
        .stock-action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .qty-field {
            width: 65px;
            padding: 10px;
            border: 2px solid var(--secondary-cream);
            border-radius: var(--radius-sm);
            text-align: center;
            font-weight: bold;
            color: var(--primary-brown);
            outline: none;
            transition: 0.2s;
        }

        .qty-field:focus {
            border-color: var(--primary-brown);
            background: var(--bg-soft);
        }

        /* ปุ่มแบบวงกลมมน */
        .btn-round {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 50%;
            /* กลมดิ๊กเหมือนไข่มุก */
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-plus {
            background: #4CAF50;
        }

        .btn-minus {
            background: #F44336;
        }

        .btn-round:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        .btn-round:active {
            transform: translateY(0);
        }

        .text-center {
            text-align: center;
        }
        /* กรุณาย้ายไปfileccsถ้าจะใช้ */
    </style>
    <x-tagbar />


    <div class="grid productcols">
        <div class="card">
            <div class="row">
                <h2 style="margin:0;">สต็อกวัตถุดิบ</h2>
                <div class="spacer"></div>
            </div>
            <div class="mini" style="margin-top:6px; color:red;">
                * ตัดสต็อกเมื่อ “รับออเดอร์” ตามสูตรสินค้า (Recipe)
                loop จาก database มาลง ใช้หน่วยเป็นunit
            </div>
            <div class="card-container">
                <div class="card-header">
                    <h3 class="title">📦 จัดการสต็อกวัตถุดิบ</h3>
                </div>
                <div class="table-responsive">
                    <table class="bubble-table">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>ชื่อวัตถุดิบ</th>
                                <th class="text-center">คงเหลือ</th>
                                <th class="text-center">ปรับปรุงจำนวน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="id-badge">01</span></td>
                                <td><strong class="item-name">ผงมาซาล่า</strong></td>
                                <td class="text-center"><span class="qty-pill">3</span></td>
                                <td>
                                    <div class="stock-action-group">
                                        <input type="number" class="qty-field" value="1" min="1">
                                        <button class="btn-round btn-plus"><span>▲</span></button>
                                        <button class="btn-round btn-minus"><span>▼</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="id-badge">02</span></td>
                                <td><strong class="item-name">ผงโบโล</strong></td>
                                <td class="text-center"><span class="qty-pill empty">0</span></td>
                                <td>
                                    <div class="stock-action-group">
                                        <input type="number" class="qty-field" value="1" min="1">
                                        <button class="btn-round btn-plus">▲</button>
                                        <button class="btn-round btn-minus">▼</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
