<x-app-layout>
    <x-tagbaradmin />

    <x-grid style="">
        <x-card>
<div class="container mt-4">

    {{-- กรอบใหญ่ --}}
    <div class="menu-wrapper">

        {{-- Header --}}
        <div class="menu-header">
            <h4>เมนู</h4>

            <a href="{{ route('adminmenu.create') }}"
               class="btn-add-menu">
                + เพิ่มเมนู
            </a>
        </div>

        {{-- Grid เมนู --}}
        <div class="menu-grid-small">

            @foreach($products as $product)
                <div class="menu-card-small">

                    {{-- ปุ่มลบ --}}
                    <form action="{{ route('adminmenu.destroy', $product->id) }}"
                    method="POST" onsubmit="return confirm('ยืนยันการลบเมนูนี้?')"
                    class="d-flex justify-content-end"> {{-- เพิ่ม Class ตรงนี้ --}}
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm ">ลบ</button>
                    </form>

                    {{-- รูป --}}
                    <div class="menu-img-box">
                        @if($product->imgurl)
                            <img src="{{ asset($product->imgurl) }}" alt="">
                        @endif
                    </div>

                    {{-- ข้อมูล --}}
                    <div class="menu-text">
                        <div>ชื่อ: {{ $product->name }}</div>
                        <div>ราคา {{ $product->price }} บาท</div>
                    </div>

                    {{-- ปุ่มล่าง --}}
                    <div class="menu-footer">
                        <form action="{{ route('adminmenu.activate', $product->id) }}" method="POST">
                        @csrf
                            <button type="submit" class="btn btn-success">เปิดขาย</button>
                        </form>

                        <button class="btn-edit"
                            data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->id }}">
                        แก้ไขเมนู
                        </button>
                    </div>
                    <div class="modal fade"
     id="editProductModal{{ $product->id }}"
     tabindex="-1">

  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;">

      {{-- Header --}}
      <div class="modal-header">
        <h5 class="modal-title">ข้อมูลสินค้า</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- Body --}}
      <form action="{{ route('adminmenu.update', $product->id) }}"
            method="POST"
            enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="modal-body">
          <div class="row">

            {{-- ซ้าย: รูป --}}
            <div class="col-md-4 text-center">
              <img src="{{ asset($product->imgurl) }}"
                   class="img-fluid rounded mb-2"
                   style="max-height:200px;">

              <input type="file" name="imgurl" class="form-control">
            </div>

            {{-- ขวา: ข้อมูล --}}
            <div class="col-md-8">

              <div class="mb-3">
                <label>ชื่อเมนู</label>
                <input type="text" name="name"
                       value="{{ $product->name }}"
                       class="form-control">
              </div>

              <div class="mb-3">
                <label>ราคา</label>
                <input type="number" step="0.01"
                       name="price"
                       value="{{ $product->price }}"
                       class="form-control">
              </div>

              <div class="mb-3">
                <label>คำอธิบาย</label>
                <textarea name="description"
                          class="form-control"
                          rows="3">{{ $product->description }}</textarea>
              </div>

            </div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            บันทึก
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
                </div>
            @endforeach

        </div>
    </div>
</div>


        </x-card>
    </x-grid>
</x-app-layout>
