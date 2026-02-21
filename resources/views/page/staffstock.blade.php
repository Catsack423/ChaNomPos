<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/adminstock.css') }}">
    <x-tagbaradmin /> <div style="display: flex; justify-content: center; padding: 40px; background-color: #fef4e8; min-height: 100vh;">
        <div class="card" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 900px;">
            
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <span style="font-size: 24px;">📦</span>
                <h2 style="color: #8b5e3c; margin: 0;">จัดการสต็อกวัตถุดิบ</h2>
            </div>

            <form action="{{ route('stock.update') }}" method="POST">
                @csrf
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: #bca08d; border-bottom: 1px solid #f5f5f5;">
                            <th style="padding: 15px;">ลำดับ</th>
                            <th>ชื่อวัตถุดิบ</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center">ปรับปรุงจำนวน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ingredients as $index => $item)
                        <tr style="border-bottom: 1px solid #fafafa;">
                            <td style="padding: 20px 15px;">
                                <span style="background: #f2e1d1; color: #8b5e3c; padding: 5px 12px; border-radius: 12px; font-size: 0.9em;">
                                    {{ sprintf('%02d', $index + 1) }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: #555;">{{ $item->name }}</strong><br>
                                <small style="color: #aaa;">{{ $item->unit }}</small>
                                <input type="hidden" name="ingredients[{{$index}}][ingredient_id]" value="{{ $item->id }}">
                            </td>
                            <td class="text-center">
                                <span style="background: #f5f5f5; padding: 8px 18px; border-radius: 15px; font-weight: bold; color: {{ ($item->inventory->quantity ?? 0) <= 0 ? 'red' : '#555' }}">
                                    {{ number_format(max($item->inventory->quantity ?? 0, 0), 0) }}
                                </span>
                                <strong style="margin-left: 10px; color: #333;">{{ $item->unit }}</strong>
                            </td>
                            <td class="text-center">
                                <div class="stock-action-group" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                    <input type="number" name="ingredients[{{$index}}][quantity]" 
                                        id="qty_{{ $index }}" class="qty-field" value="0"
                                        style="width: 60px; text-align: center; border-radius: 8px; border: 1px solid #ddd;">
                                    
                                    <button type="button" onclick="adjustInput('{{$index}}', 1)" 
                                        style="background: #4CAF50; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">▲</button>
                                    
                                    <button type="button" onclick="adjustInput('{{$index}}', -1)" 
                                        style="background: #F44336; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">▼</button>
                                    
                                    </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" style="background: #8b5e3c; color: white; border: none; padding: 12px 35px; border-radius: 12px; cursor: pointer; font-weight: bold; font-size: 1em;">
                        บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ฟังก์ชันปรับค่าใหม่: กดแดงเลขจะลดลงเรื่อยๆ จนติดลบ
        function adjustInput(index, amount) {
            const input = document.getElementById('qty_' + index);
            let currentVal = parseInt(input.value) || 0;
            
            // บวกหรือลบตามค่า amount ที่ส่งมา (+1 หรือ -1)
            input.value = currentVal + amount;
        }

        function confirmDelete(id, name) {
            if (confirm(`คุณต้องการลบวัตถุดิบ "${name}" ใช่หรือไม่?`)) {
                const form = document.getElementById('delete-form');
                form.action = `/admin/stock/delete/${id}`;
                form.submit();
            }
        }
    </script>
</x-app-layout>