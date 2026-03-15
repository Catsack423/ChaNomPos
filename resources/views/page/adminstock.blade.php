<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/adminstock.css') }}">

    <x-tagbaradmin />

    <div class="grid stockcols">
        {{-- ฝั่งซ้าย: รายชื่อวัตถุดิบ และ สถานะสต็อก --}}
        <div class="left-column">

            {{-- ส่วนที่ 1: รายชื่อวัตถุดิบ (คำนวณจาก Real_ingrediant) --}}
            <div class="card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <h2 style="margin-bottom: 20px; font-weight: bold">รายชื่อวัตถุดิบ</h2>

                <form action="{{ route('stock.update') }}" method="POST" id="mainStockForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="bubble-table" style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
                            <thead>
                                <tr style="color: #8b5e3c;">
                                    <th>ลำดับ</th>
                                    <th>ชื่อวัตถุดิบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ingredients as $index => $item)
                                    <tr style="background: #fdfaf8; border-radius: 10px;">
                                        <td style="padding: 15px;">
                                            <span class="id-badge" style="background: #e6d5c3; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
                                                {{ sprintf('%02d', $index + 1) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="item-name">{{ $item->name }}</strong><br>
                                            <small class="mini" style="color: #999;">{{ $item->unit }}</small>
                                            <input type="hidden" name="ingredients[{{ $index }}][ingredient_id]" value="{{ $item->id }}">
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            {{-- ส่วนที่ 2: กำลังใช้งาน & คลัง (ดีไซน์ใหม่) --}}
            <div class="stock-container">
                {{-- กำลังใช้งาน (แสดงเฉพาะที่เปิดถังแล้วและยังไม่หมด) --}}
                <div class="section-card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px;">
                    <div class="section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <span style="font-weight: bold; font-size: 1.1rem;">กำลังใช้งาน <span style="color:#8bc34a; font-size:0.8rem; font-weight:normal;">(เปิดถังแล้ว)</span></span>
                        {{-- ลิ้งค์ไปหน้าเพิ่ม Lot ใหม่ --}}
                        <button class="btn-add" onclick="openAddModal()"
style="background:#7B4A2E;color:white;border:none;padding:5px 15px;border-radius:20px;font-size:0.8rem;cursor:pointer;">
➕ เพิ่มสินค้า
</button>
                   </div>

                    <table class="figma-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; color: #888; font-size: 0.85rem; border-bottom: 1px solid #eee;">
                                <th colspan="2" style="padding: 10px;">ชื่อล็อต</th>
                                <th>วัตถุดิบระบบ</th>
                                <th>หมดอายุ (นับถอยหลัง)</th>
                                <th style="width: 150px; text-align: right;">คงเหลือ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($activeLots) && $activeLots->count() > 0)
    @foreach($activeLots as $lot)
        @php
            $remain = $lot->remaining();
            $percent = $lot->quantity > 0 ? round(($remain / $lot->quantity) * 100) : 0;
            $progressColor = $percent < 30 ? '#ff4d4d' : '#8bc34a';
        @endphp

        {{-- เพิ่มเงื่อนไข id="lot-row-{{ $lot->id }}" และ style display ตามค่า percent --}}
        <tr id="lot-row-{{ $lot->id }}"
            class="active-lot-row"
            data-percent="{{ $percent }}"
            style="border-bottom: 1px solid #f9f9f9; {{ $percent <= 0 ? 'display: none;' : '' }}">

            <td style="width: 45px; padding: 10px 5px;">
                <img src="{{ $lot->imgurl ? asset('img/'.$lot->imgurl) : asset('img/logo.png') }}"
width="60"
height="60"
style="border-radius:8px;object-fit:cover;">
            </td>
            <td>
                {{-- แก้ไขชื่อล็อต: ให้แสดงชื่อของวัตถุดิบแทนคำว่า Lot # มั่วๆ --}}
                <div style="font-weight: bold; font-size: 0.9rem;">{{ $lot->name ?? 'ไม่ระบุสินค้า' }}</div>
                <small style="color: #999;">รหัสล็อต: #{{ $lot->id }}</small>
            </td>
            <td style="color:#666; font-size: 0.85rem;">{{ $lot->ingredient->name }}</td>
            <td>
                {{ $lot->expried ? \Carbon\Carbon::parse($lot->expried)->diffForHumans() : '-' }}
            </td>
            <td>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 0.8rem; margin-bottom: 4px;">
                    <span>{{ number_format(max($remain, 0), 2) }}</span>
                    <span>{{ $percent }}%</span>
                </div>
                <div style="background: #eee; height: 6px; border-radius: 10px; overflow: hidden;">
                    <div style="width: {{ $percent }}%; background: {{ $progressColor }}; height: 100%;"></div>
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr><td colspan="5" class="text-center" style="padding:20px; color:#ccc;">ไม่มีวัตถุดิบที่กำลังเปิดใช้งาน</td></tr>
@endif
                        </tbody>
                    </table>
                </div>

                {{-- คลัง (ของที่ยังไม่กดเปิดใช้งาน in_use = 0) --}}
                <div class="section-card" style="background:white;padding:20px;border-radius:15px;">
<div class="section-title" style="font-weight:bold;margin-bottom:15px;">
คลัง (รอเปิดใช้งาน)
</div>

<table style="width:100%;border-collapse:collapse;">

@foreach($stockLots as $lot)

<tr style="border-bottom:1px solid #f9f9f9;">

<td style="width:45px;padding:10px 5px;">
<img src="{{ $lot->imgurl ? asset('img/'.$lot->imgurl) : asset('img/logo.png') }}"
width="35"
height="35"
style="border-radius:8px;object-fit:cover;">
</td>

<td style="font-weight:bold;font-size:0.9rem;width:30%;">
{{ $lot->name ?? 'ไม่ระบุสินค้า' }}
</td>

<td style="color:#888;font-size:0.85rem;width:30%;">
{{ optional($lot->ingredient)->name }}
</td>

<td style="text-align:right;font-size:0.85rem;color:#666;">
รอเปิดใช้งาน
</td>

</tr>

@endforeach

</table>


</div>
            </div>
        </div>

        {{-- ฝั่งขวา: ฟอร์มเพิ่มวัตถุดิบระบบ --}}
        <div class="right-column">
            <div class="card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); height: fit-content;">
                <h3 style="margin-bottom: 20px; font-weight: bold">เพิ่มประเภทวัตถุดิบ</h3>
                <form action="{{ route('admin.stock.add') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <input type="text" name="name" class="input" placeholder="ชื่อวัตถุดิบ (เช่น นมข้น, ผงชา)" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <input type="text" name="unit" class="input" placeholder="หน่วย (เช่น ml, g, ชิ้น)" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    <button type="submit" style="width: 100%; background: #7B4A2E; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: bold;">สร้างประเภทวัตถุดิบ</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ส่วนที่ 3: ประวัติการเปลี่ยนแปลง --}}
    <div class="grid logcols" style="margin-top: 30px;">
        <div class="card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 20px; font-size: 1.1rem; color: #000; font-weight: bold">ประวัติการใช้สต็อกล่าสุด</h2>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="figma-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                        <tr style="text-align: left; color: #888; font-size: 0.85rem; border-bottom: 2px solid #fdfaf8;">
                            <th colspan="2" style="padding: 10px;">วัตถุดิบ</th>
                            <th>วัน-เวลา</th>
                            <th>จำนวน</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach ($logs as $log)

@php
$ingredientName = $log->real_ingredient->ingredient->name ?? 'ไม่พบ';
@endphp

<tr style="border-bottom:1px solid #eee;">

<td style="width:45px;padding:10px 5px;">
<img src="{{ $log->real_ingredient && $log->real_ingredient->imgurl
? asset('img/'.$log->real_ingredient->imgurl)
: asset('img/logo.png') }}"
width="35"
height="35"
style="border-radius:8px;object-fit:cover;">
</td>

<td>

<div style="font-weight:bold;font-size:0.9rem;">
{{ $ingredientName }}
</div>

</td>

<td style="color:#666;font-size:0.85rem;">
{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M H:i') }}
</td>

<td style="font-weight:bold;font-family:monospace;">
{{ number_format($log->quantity,2) }}
</td>

<td>

<div style="display:flex;align-items:center;gap:8px;">

@if($log->action == 'add')

<span style="background:#e8f5e9;color:#2e7d32;
padding:3px 10px;border-radius:5px;font-size:0.75rem;font-weight:bold;">
➕ เพิ่ม
</span>

@else

<span style="background:#ffebee;color:#c62828;
padding:3px 10px;border-radius:5px;font-size:0.75rem;font-weight:bold;">
➖ ลบ
</span>

@endif

<span style="color:#666;font-size:0.85rem;">
โดย {{ $log->user->name ?? 'System' }}
</span>
</div>
</td>
</tr>
@endforeach
</tbody>

                </table>
            </div>
        </div>
    </div>

    {{-- Hidden Form for Delete --}}
    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function adjustInput(index, amount) {
            const input = document.getElementById('qty_' + index);
            let currentVal = parseInt(input.value) || 0;
            input.value = currentVal + amount;
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: `คุณต้องการลบ<br><span style="font-size: 18px; color: #7b4a2e;">"${name}"</span><br>ออกจากระบบถาวรหรือไม่?`,
                html: `<div style=" color: #d33; font-weight: bold; font-size: 0.9rem;">⚠️ คำเตือน: ข้อมูล Log และ Lot ทั้งหมดจะถูกลบออกและไม่สามารถเรียกคืนได้!</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#4CAF50',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = `/admin/stock/delete/${id}`;
                    form.submit();
                }
            });
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ค้นหาแถวทั้งหมดในตารางกำลังใช้งาน
        const rows = document.querySelectorAll('.active-lot-row');

        rows.forEach(row => {
            const percent = parseInt(row.getAttribute('data-percent'));
            // ถ้าเปอร์เซ็นต์น้อยกว่าหรือเท่ากับ 0 ให้ลบ Element ออกจากหน้าจอเลย
            if (percent <= 0) {
                row.remove();
            }
        });

        // ถ้าลบจนแถวว่าง ให้แสดงข้อความว่าไม่มีข้อมูล
        const tbody = document.querySelector('.figma-table tbody');
        if (tbody && tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding:20px; color:#ccc;">ไม่มีวัตถุดิบที่กำลังเปิดใช้งาน</td></tr>';
        }
    });
</script>

{{-- modal --}}
<div id="addLotModal" class="modal">

<div class="modal-box">

<div class="modal-header">
<h2>เพิ่มสินค้าเข้าคลัง</h2>
<span class="close-btn" onclick="closeAddModal()">✕</span>
</div>

<form id="addLotForm" enctype="multipart/form-data">
@csrf

<div class="upload-box">

<label>รูปสินค้า</label>

<br>

<img id="previewImage"
src="{{ asset('img/logo.png') }}"
class="preview-img">

<input type="file" name="imgurl" id="imgInput" hidden>

<p class="upload-text">แตะที่รูปเพื่อเลือกรูป</p>

</div>

<div class="form-grid">

<div>
<label>ชื่อสินค้า</label>
<input type="text" name="name" placeholder="กรอกชื่อสินค้า" required>
</div>

<div>
<label>วัตถุดิบในระบบ</label>
<select name="ingredient_id">
<option value="">เลือกวัตถุดิบ</option>

@foreach($ingredients as $ingredient)

<option value="{{ $ingredient->id }}">
{{ $ingredient->name }}
</option>

@endforeach

</select>
</div>

<div>
<label>จำนวนสินค้า</label>
<input type="number" name="quantity" placeholder="เช่น 10">
</div>

<div>
<label>วันหมดอายุ</label>
<input type="datetime-local" name="expired">
</div>

</div>

<div class="modal-footer">

<button type="submit" class="btn-save">
บันทึกข้อมูล
</button>

</div>

</form>

</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(){

// เปิด modal
window.openAddModal = function(){
document.getElementById('addLotModal').style.display='flex';
}

// ปิด modal
window.closeAddModal = function(){
document.getElementById('addLotModal').style.display='none';
}

// กดพื้นหลังปิด modal
document.getElementById("addLotModal").addEventListener("click", function(e){
if(e.target === this){
closeAddModal();
}
})

// กดรูปเพื่อเลือกรูป
document.getElementById("previewImage").addEventListener("click", function(){
document.getElementById("imgInput").click();
});

// preview รูป
document.getElementById("imgInput").addEventListener("change", function(){

let file = this.files[0];

if(file){

let reader = new FileReader();

reader.onload = function(e){
document.getElementById("previewImage").src = e.target.result;
}

reader.readAsDataURL(file);

}

});

// submit
document.getElementById("addLotForm").addEventListener("submit", function(e){

e.preventDefault();

let formData = new FormData(this);

fetch("{{ route('stock.addLot') }}", {

method: "POST",
body: formData,
headers:{
"X-CSRF-TOKEN": "{{ csrf_token() }}"
}

})
.then(res => res.json())
.then(data => {

    if(data.status === "success"){
        alert("เพิ่มสินค้าเรียบร้อย");
        location.reload();
    }else{
        alert(data.message);
    }

})
.catch(err=>{
    console.error(err);
    alert("เกิดข้อผิดพลาด");
});

});

});
</script>



</x-app-layout>
