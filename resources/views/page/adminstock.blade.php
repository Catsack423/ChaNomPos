<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/adminstock.css') }}">

    <x-tagbaradmin />

    <div class="grid stockcols">

        <div class="card"
            style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 20px; font-weight: bold">รายชื่อวัตถุดิบ</h2>

            <form action="{{ route('stock.update') }}" method="POST" id="mainStockForm">
                @csrf
                <div class="table-responsive">
                    <table class="bubble-table" style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
                        <thead>
                            <tr style="color: #8b5e3c;">
                                <th>ลำดับ</th>
                                <th>ชื่อวัตถุดิบ</th>
                                <th class="text-center">คงเหลือ</th>
                                <th class="text-center">ปรับปรุงจำนวน</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ingredients as $index => $item)
                                <tr style="background: #fdfaf8; border-radius: 10px;">
                                    <td style="padding: 15px;">
                                        <span class="id-badge"
                                            style="background: #e6d5c3; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
                                            {{ sprintf('%02d', $index + 1) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="item-name">{{ $item->name }}</strong><br>
                                        <small class="mini" style="color: #999;">{{ $item->unit }}</small>
                                        <input type="hidden" name="ingredients[{{ $index }}][ingredient_id]"
                                            value="{{ $item->id }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="qty-pill"
                                            style="background: #eee; padding: 8px 15px; border-radius: 20px; font-weight: bold;">
                                            {{ number_format(max($item->inventory->quantity ?? 0, 0), 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="stock-action-group"
                                            style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                            <input type="number" name="ingredients[{{ $index }}][quantity]"
                                                id="qty_{{ $index }}" class="qty-field" value="0"
                                                style="width: 100px; text-align: center; border-radius: 8px; border: 1px solid #ddd;">

                                            <button type="button" onclick="adjustInput('{{ $index }}', 1)"
                                                style="background: #4CAF50; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">▲</button>

                                            <button type="button" onclick="adjustInput('{{ $index }}', -1)"
                                                style="background: #F44336; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">▼</button>

                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                            onclick="confirmDelete('{{ $item->id }}', '{{ $item->name }}')"
                                            style="border: 1px solid #ff4d4d; color: #ff4d4d; background: #fff5f5; padding: 5px 12px; border-radius: 8px; font-size: 0.9em; cursor: pointer;">
                                            ลบ
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn-save"
                        style="background: #7B4A2E; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-weight: bold;">
                        บันทึกการเปลี่ยนแปลงทั้งหมด
                    </button>
                </div>
            </form>
        </div>

        <div class="card"
            style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); height: fit-content;">
            <h3 style="margin-bottom: 20px; font-weight: bold">เพิ่มวัตถุดิบ </h3>
            <form action="{{ route('admin.stock.add') }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <input type="text" name="name" class="input" placeholder="ชื่อวัตถุดิบ" required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <input type="text" name="unit" class="input"
                        placeholder="ปริมาณต่อ unit (เช่น ml, scoop, ชิ้น)" required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                <div style="margin-bottom: 20px;">
                    <input type="number" name="initial_quantity" class="input" placeholder="ปริมาณเริ่มต้น"
                        value="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                <button type="submit"
                    style="width: 100%; background: #7B4A2E; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: bold;">
                    บันทึก
                </button>
            </form>
        </div>
    </div>

    <div style="" class="grid logcols">
        <div class="card">
            <h2 style="margin-bottom: 10px; font-size: 1.2rem; color: #000000; font-weight: bold">บันทึกการเปลี่ยนแปลง
            </h2>

            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="bubble-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                        <tr style="text-align: left; border-bottom: 2px solid #fdfaf8;">
                            <th style="padding: 8px;">วัน-เวลา</th>
                            <th>วัตถุดิบ</th>
                            <th>การกระทำ</th>
                            <th class="text-right">จำนวน</th>
                            <th>พนักงาน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px; color: #666;">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                                <td style="font-weight: bold;">{{ $log->ingredient->name ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        style="color: {{ $log->action == 'add' ? '#4CAF50' : '#F44336' }}; font-weight: bold;">
                                        {{ $log->action == 'add' ? 'เพิ่มสต็อก' : 'ลดสต็อก' }}
                                    </span>
                                </td>
                                <td class="text-right" style="font-family: monospace;">
                                    {{ number_format($log->quantity, 2) }}</td>
                                <td style="color: #888;">{{ $log->user->name ?? 'System' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        // ฟังก์ชันปรับค่าใหม่: กดแดงเลขจะลดลงเรื่อยๆ จนติดลบ
        function adjustInput(index, amount) {
            const input = document.getElementById('qty_' + index);
            let currentVal = parseInt(input.value) || 0;

            // บวกหรือลบตามค่า amount ที่ส่งมา (+1 หรือ -1)
            input.value = currentVal + amount;
        }

        function confirmDelete(id, name) {
            Swal.fire({
                // ใช้ <br> และ <span> เพื่อปรับขนาดฟอนต์ของชื่อประเภทให้เล็กลงตามที่คุณต้องการ
                title: `คุณต้องการลบ<br><span style="font-size: 18px; color: #7b4a2e;">"${name}"</span><br>ออกจากระบบถาวรหรือไม่?`,
                html: `
            <div style=" color: #d33; font-weight: bold; font-size: 0.9rem;">
                ⚠️ คำเตือน: ข้อมูลใน Log ทั้งหมดที่เกี่ยวข้องจะถูกลบออกด้วย 
                และไม่สามารถเรียกคืนได้!
            </div>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // สีน้ำตาลธีม Pos ChaNom
                cancelButtonColor: '#4CAF50 ',
                confirmButtonText: 'DELETE',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                scrollbarPadding: false // ป้องกัน Navbar ยืดออก
                    ,
                customClass: {
                    popup: 'swal-small-popup',
                    title: 'swal-small-title',
                    confirmButton: 'swal-small-button',
                    cancelButton: 'swal-small-button'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // ถ้าผู้ใช้กดยืนยัน
                    const form = document.getElementById('delete-form');
                    form.action = `/admin/stock/delete/${id}`;
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
