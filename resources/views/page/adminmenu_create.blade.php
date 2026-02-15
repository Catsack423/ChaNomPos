<x-app-layout>
    <x-tagbaradmin />

    <x-grid style="">
        <x-card>
            <div class="container mt-4">

    <div class="card p-4 shadow-sm" style="border-radius:16px; max-width:500px; margin:auto;">

        <h4 class="mb-4">เพิ่มเมนูใหม่</h4>

        <form action="{{ route('adminmenu.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="mb-3">
                <label class="form-label">ชื่อเมนู</label>
                <input type="text" name="name"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" step="0.01"
                       name="price"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" name="imgurl" class="form-control" required>
            </div>

            <div class="mb-3">
            <label class="form-label">รายละเอียด</label>
            <textarea name="description"
                class="form-control"
                rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('adminmenu') }}"
                   class="btn btn-secondary">
                    ยกเลิก
                </a>

                <button type="submit"
                        class="btn btn-primary">
                    บันทึก
                </button>
            </div>

        </form>

    </div>

</div>

        </x-card>
    </x-grid>
</x-app-layout>
