<x-app-layout>
    <x-tagbaradmin />

    <x-grid style="">
        <x-card>
            <style>
.category-form,
.recipe-form {
    display: grid !important;
    grid-template-columns: 1fr auto;
    gap: 10px;
}

.recipe-form {
    grid-template-columns: 1.4fr 1fr auto;
}

.form-input,
.form-select {
    margin: 5px;
    padding: 10px 12px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
}
.modal-footer-custom {
    padding: 20px;
    border-top: 1px solid #eee;
    text-align: right;
}


</style>

<script>
function updateIngredientOptions() {
    const selects = document.querySelectorAll(
        '#recipeContainer select[name="ingredients[]"]'
    );

    const selectedValues = Array.from(selects)
        .map(s => s.value)
        .filter(v => v !== '');

    selects.forEach(select => {
        Array.from(select.options).forEach(option => {
            if (
                option.value !== '' &&
                selectedValues.includes(option.value) &&
                option.value !== select.value
            ) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });
    });
}


function uploadImage(input) {

    if (!input.files || !input.files.length) return;

    const form = input.form;   // ✅ form ที่ถูกต้อง
    const wrapper = input.closest('.image-upload');
    const preview = wrapper.querySelector('img');

    /* ===== PREVIEW ===== */
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);

    /* ===== UPLOAD ===== */
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            'X-HTTP-Method-Override': 'PATCH'
        },
        body: formData
    })
    .then(res => res.json())
    .then(() => {
        console.log('อัปโหลดสำเร็จ');
    })
    .catch(console.error);
}

function addRecipeRow() {

    const container = document.getElementById('recipeContainer');
    const firstRow = container.querySelector('.recipe-form');

    if (firstRow) {
        const btn = firstRow.querySelector('button');

        btn.setAttribute('class', 'x-delete-btn');
        btn.innerText = '✕';
        btn.onclick = () => {
            firstRow.remove();
            updateIngredientOptions();
        };
    }

    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('select').value = '';
    newRow.querySelector('input').value = '';

    const newBtn = newRow.querySelector('button');
    newBtn.setAttribute('class', 'btn-add');
    newBtn.innerText = '+ เพิ่ม';
    newBtn.onclick = addRecipeRow;

    container.insertBefore(newRow, container.firstChild);

    updateIngredientOptions();
}

</script>


<div class="container mt-4">

    {{-- กรอบใหญ่ --}}
    <div class="menu-wrapper">

        {{-- Header --}}
        <div class="menu-header">
            <h4>เมนู</h4>

            <<button data-bs-toggle="modal"
        data-bs-target="#createMenuModal"
        class="btn-add-menu">+ เพิ่มเมนู
            </button>
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
                    <button type="submit" class="x-delete-btn" title="ลบ">✕</button>
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
                        <form action="{{ route('adminmenu.toggle', $product->id) }}" method="POST">
                        @csrf
                    @method('PATCH')

                    <label class="switch">
                    <input type="checkbox" onchange="this.form.submit()" {{ $product->is_active ? 'checked' : '' }}>
                    <span class="slider round"></span>
                    </label>
                        </form>

                        <button type="button" class="edit-btn"
                            data-bs-toggle="modal" data-bs-target="#recipeModal-{{ $product->id }}">
                        แก้ไขเมนู
                        </button>
                    </div>
                </div>
            @endforeach
            {{-- ================= MODAL ZONE ================= --}}
{{-- ================= MODAL ZONE ================= --}}
@foreach ($products as $product)
<div class="modal fade" id="recipeModal-{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content recipe-modal">

            {{-- ปุ่มปิด --}}
            <button type="button"
                    class="btn-close modal-close"
                    data-bs-dismiss="modal"></button>

            {{-- BODY --}}
            <div class="recipe-modal-body">

                {{-- ================= LEFT ================= --}}
                <div class="recipe-left">

                    {{-- รูป --}}
                    <form method="POST"
      action="{{ route('adminmenu.updateimgmodal', $product->id) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    <label class="image-upload">
        <img src="{{ asset($product->imgurl) }}">
        <span class="choose-image-text">เลือกรูป</span>

        <input type="file"
               name="image"
               hidden
               accept="image/*"
               onchange="uploadImage(this)">
    </label>
</form>

                    {{-- ประเภทสินค้า --}}
                    <div class="category-box">
                        <h6 class="section-title">ประเภทสินค้า</h6>

                        <form method="POST"
                              action="{{ route('adminmenu.category.add', $product->id) }}"
                              class="category-add-form">
                            @csrf
                            <input type="text" name="name"
                                   placeholder="ชื่อประเภทสินค้า" class="form-input" required>
                            <button type="submit" class="btn-add">+ เพิ่ม</button>
                        </form>

                        @foreach ($categories as $category)
        <div class="cat-item" style="display:flex; align-items:center; gap:8px;">

            {{-- Checkbox --}}
            <input type="checkbox"
                   name="category_ids[]"
                   value="{{ $category->id }}"
                   {{ isset($product) && $product->categories->contains($category->id) ? 'checked' : '' }}
                   onchange="toggleCategory(
                            this,
                            {{ isset($product) ? $product->id : 'null' }},
                            {{ $category->id }})">
            {{-- Name --}}
            <span style="flex:1;">{{ $category->name }}</span>

            {{-- ปุ่มลบ DB --}}
            <form method="POST"
                  action="{{ route('adminmenu.category.delete', $category->id) }}"
                  onsubmit="return confirm('ลบประเภทนี้ออกจากระบบถาวร ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="x-delete-btn">✕</button>
            </form>

        </div>
        @endforeach
                    </div>
                </div>

                {{-- ================= RIGHT ================= --}}
                <div class="recipe-right">

                    {{-- ข้อมูลสินค้า --}}
                    <form id="productForm-{{ $product->id }}" method="POST" action="{{ route('adminmenu.update', $product->id) }}" class="product-form">
                    @csrf
                    @method('PUT')

                        <h5 class="section-title">ข้อมูลสินค้า</h5>

                        <input type="text" name="name"
                               value="{{ $product->name }}" required>

                        <input type="number" name="price"
                               value="{{ $product->price }}" required>

                        <textarea name="description" required>{{ $product->description }}</textarea>
                    </form>

                    <hr>

                    {{-- สูตรสินค้า --}}
                    <h5 class="section-title" style="margin: 10px">สูตรสินค้า</h5>

                    <form class="recipe-form"
                          method="POST"
                          action="{{ route('adminmenu.storemodal') }}">
                        @csrf
                        <input type="hidden" name="product_id"
                               value="{{ $product->id }}">

                        <select name="ingredient_id" required class="form-select">
                            <option value="">-- เลือกวัตถุดิบ --</option>
                            @foreach ($ingredients as $ing)
                                <option value="{{ $ing->id }}"data-unit="{{ $ing->unit }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                            @endforeach
                        </select>

                        <input type="number" name="amount"
                               placeholder="จำนวน" required class="form-input">

                        <button type="submit">+เพิ่ม</button>
                    </form>

                    {{-- รายการสูตร --}}
                    <div class="recipe-list">
                        @foreach($product->recipes as $recipe)
                        <span class="recipe-chip">
                            {{ $recipe->ingredient->name  }}
</option>
                            {{ $recipe->amount }} {{ $recipe->ingredient->unit }}

                            <form method="POST"
                                  action="{{ route('adminmenu.destroymodal',$recipe->id) }}">
                                @csrf
                                @method('DELETE')
                                <button class="x-delete-btn">✕</button>
                            </form>
                        </span>
                        @endforeach
                    </div>



                </div>
            </div>

            {{-- ปุ่มบันทึก --}}
                    <button type="submit" form="productForm-{{ $product->id }}" class="save-btn">บันทึก</button>
        </div>
    </div>
</div>
@endforeach


{{-- ================= MODAL CREATE MENU ================= --}}
<div class="modal fade" id="createMenuModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content recipe-modal">

            <button type="button"
                    class="btn-close modal-close"
                    data-bs-dismiss="modal"></button>

            {{-- FORM เพิ่มเมนู --}}
            <form id="createProductForm"
                  method="POST"
                  action="{{ route('adminmenu.store') }}"
                  enctype="multipart/form-data"  class="create-form">

                @csrf

                <div class="recipe-modal-body">

                    {{-- ================= LEFT ================= --}}
                    <div class="recipe-left">

                        {{-- รูป --}}
                        <label class="image-upload">
                            <img id="createPreviewImage"
                                 src="{{ asset('img/logo.png') }}"
                                 alt="preview">
                            <span class="choose-image-text">เลือกรูป</span>

                            <input type="file"
                                   name="image"

                                   id="createImageInput"
                                   accept="image/*"
                                   hidden>
                        </label>

                        {{-- ประเภทสินค้า --}}
                        <div class="category-box">
                            <h6 class="section-title">ประเภทสินค้า</h6>

                            <div id="categoryContainer">
                                <div style="display:flex; gap:8px;">
                                <input type="text"
                                    id="newCategoryName"
                                    placeholder="เพิ่มประเภทใหม่"
                                    class="form-input">

                                <button type="button"
                                        class="btn-add"
                                        onclick="createCategoryAjax()">
                                    + เพิ่ม
                                </button>
                            </div>
                                {{-- เลือกประเภทที่มีอยู่แล้ว --}}
                                        <div id="categoryList">

                                            @foreach ($categories as $category)
                                            <div class="cat-item"
                                                data-id="{{ $category->id }}"
                                                style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">

                                                <input type="checkbox"
                                                    name="category_ids[]"
                                                    value="{{ $category->id }}">

                                                <span style="flex:1;">{{ $category->name }}</span>

                                                <button type="button"
                                                        onclick="deleteCategory({{ $category->id }})"
                                                        class="x-delete-btn">
                                                    ✕
                                                </button>
                                            </div>
                                            @endforeach

                                        </div>
                                 </div>
                        </div>

                    </div>

                    {{-- ================= RIGHT ================= --}}
                    <div class="recipe-right">

                        <h5 class="section-title">ข้อมูลสินค้า</h5>

                        <input type="text"
                               name="name"
                               placeholder="ชื่อเมนู"
                               required
                               class="form-input">

                        <input type="number"
                               name="price"
                               placeholder="ราคา"
                               required
                               class="form-input">

                        <textarea name="description"
                                  placeholder="รายละเอียด"
                                  required
                                  class="form-input"></textarea>

                        <hr>

                        {{-- สูตร --}}
                        <h5 class="section-title" style="margin: 10px">สูตรสินค้า</h5>

                        <div id="recipeContainer">
                            <div class="recipe-form">
                                <select name="ingredients[]"
                                        class="form-select"
                                        >
                                    <option value="">-- เลือกวัตถุดิบ --</option>
                                    @foreach ($ingredients as $ing)
                                        <option value="{{ $ing->id }}">
                                            {{ $ing->name }} ({{ $ing->unit }})
                                        </option>
                                    @endforeach
                                </select>

                                <input type="number"
                                       name="amounts[]"
                                       placeholder="จำนวน"
                                       class="form-input"
                                       >

                                <button type="button"
                                        class="btn-add"
                                        onclick="addRecipeRow()">
                                    + เพิ่ม
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </form>
            @foreach ($categories as $category)
<form id="delete-cat-{{ $category->id }}"
      method="POST"
      action="{{ route('adminmenu.category.delete', $category->id) }}"
      style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endforeach
            {{-- ปุ่มบันทึก --}}
            <button type="submit" form="createProductForm" class="save-btn">บันทึก</button>
        </div>
    </div>
</div>

<script>
document.getElementById('createImageInput')
    .addEventListener('change', function (event) {

    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('createPreviewImage').src = e.target.result;
    };
    reader.readAsDataURL(file);
});
</script>

<script>

document.addEventListener('change', function (e) {
    if (e.target.name === 'ingredients[]') {
        updateIngredientOptions();
    }
});
</script>

<script>
function toggleCategory(checkbox, productId, categoryId) {
    fetch(`/adminmenu/product/${productId}/category/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            category_id: categoryId,
            checked: checkbox.checked
        })
    }).catch(() => {
        // ถ้า error → revert checkbox
        checkbox.checked = !checkbox.checked;
        alert('เกิดข้อผิดพลาด');
    });
}
</script>
{{-- AJAX catagory --}}
<script>

function createCategoryAjax() {

    let name = document.getElementById('newCategoryName').value;

    if (!name) {
        alert('กรุณากรอกชื่อประเภท');
        return;
    }

    fetch("{{ route('adminmenu.category.ajaxStore') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ name: name })
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {

            let category = data.category;

            let html = `
                <div class="cat-item"
                     data-id="${category.id}"
                     style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">

                    <input type="checkbox"
                           name="category_ids[]"
                           value="${category.id}"
                           checked>

                    <span style="flex:1;">${category.name}</span>

                    <button type="button"
                            onclick="deleteCategory(${category.id})"
                            class="x-delete-btn">
                        ✕
                    </button>
                </div>
            `;

            document.getElementById('categoryList')
                    .insertAdjacentHTML('beforeend', html);

            document.getElementById('newCategoryName').value = '';
        }
    });
}



function deleteCategory(id) {

    if (!confirm('ลบประเภทนี้ออกจากระบบ ?')) return;

    fetch(`/adminmenu/category/ajax-delete/${id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            document.querySelector(`[data-id='${id}']`).remove();
        }
    });
}

</script>


        </x-card>
    </x-grid>
</x-app-layout>
