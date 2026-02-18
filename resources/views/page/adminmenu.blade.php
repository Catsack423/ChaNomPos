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

            <div class="container mt-4">
                {{-- กรอบใหญ่ --}}
                <div class="menu-wrapper">
                    <div class="menu-header">
                        <h4>เมนู</h4>
                        <button onclick="openMenuModal('create')" class="btn-add-menu">+ เพิ่มเมนู</button>
                    </div>

                    {{-- Grid เมนู --}}
                    <div class="menu-grid-small">
                        @foreach($products as $product)
                            <div class="menu-card-small">
                                {{-- ปุ่มลบ --}}
                                <form action="{{ route('adminmenu.destroy', $product->id) }}"
                                      method="POST" onsubmit="return confirm('ยืนยันการลบเมนูนี้?')"
                                      class="d-flex justify-content-end">
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

                                    {{-- ปุ่มแก้ไข ดึงข้อมูลใส่ data-* attributes --}}
                                    <button type="button" class="edit-btn"
                                            data-product="{{ json_encode($product) }}"
                                            data-categories="{{ json_encode($product->categories->pluck('id')) }}"
                                            data-recipes="{{ json_encode($product->recipes) }}"
                                            onclick="openMenuModal('edit', this)">
                                        แก้ไขเมนู
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= MODAL หลักอันเดียว ================= --}}
            <div class="modal fade" id="mainMenuModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content recipe-modal">

                        <button type="button" class="btn-close modal-close" data-bs-dismiss="modal"></button>

                        <form id="mainMenuForm" method="POST" action="" enctype="multipart/form-data" class="create-form">
                            @csrf
                            <input type="hidden" name="_method" id="formMethod" value="POST">

                            <div class="recipe-modal-body">
                                {{-- LEFT --}}
                                <div class="recipe-left">
                                    <label class="image-upload">
                                        <img id="previewImage" src="{{ asset('img/logo.png') }}" alt="preview">
                                        <span class="choose-image-text">เลือกรูป</span>
                                        <input type="file" name="image" id="imageInput" accept="image/*" hidden onchange="previewUploadImage(this)">
                                    </label>

                                    <div class="category-box">
                                        <h6 class="section-title">ประเภทสินค้า</h6>
                                        <div id="categoryContainer">
                                            <div style="display:flex; gap:8px;">
                                                <input type="text" id="newCategoryName" placeholder="เพิ่มประเภทใหม่" class="form-input">
                                                <button type="button" class="btn-add" onclick="createCategoryAjax()">+ เพิ่ม</button>
                                            </div>
                                            <div id="categoryList">
                                                @foreach ($categories as $category)
                                                <div class="cat-item" data-id="{{ $category->id }}" style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="category-checkbox">
                                                    <span style="flex:1;">{{ $category->name }}</span>
                                                    <button type="button" onclick="deleteCategory({{ $category->id }})" class="x-delete-btn">✕</button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT --}}
                                <div class="recipe-right">
                                    <h5 class="section-title" id="modalTitleText">ข้อมูลสินค้า</h5>

                                    <input type="text" name="name" id="productName" placeholder="ชื่อเมนู" required class="form-input">
                                    <input type="number" name="price" id="productPrice" placeholder="ราคา" required class="form-input">
                                    <textarea name="description" id="productDesc" placeholder="รายละเอียด" required class="form-input"></textarea>

                                    <hr>

                                    <h5 class="section-title" style="margin: 10px">สูตรสินค้า</h5>
                                    <div id="recipeContainer"></div>
                                    <button type="button" class="btn-add" onclick="addRecipeRow()" style="margin-top: 10px; display: block;">+ เพิ่มวัตถุดิบ</button>
                                </div>
                            </div>
                            <button type="submit" class="save-btn">บันทึก</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Template สำหรับ Select วัตถุดิบ --}}
            <div id="recipeTemplate" style="display: none;">
                <div class="recipe-form recipe-row">
                    <select name="ingredients[]" class="form-select ingredient-select" onchange="updateIngredientOptions()">
                        <option value="">-- เลือกวัตถุดิบ --</option>
                        @foreach ($ingredients as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="amounts[]" placeholder="จำนวน" class="form-input amount-input" step="0.01">
                    <button type="button" class="x-delete-btn" onclick="removeRecipeRow(this)">✕</button>
                </div>
            </div>

            <script>
                const storeRoute = "{{ route('adminmenu.store') }}";
                const baseAppUrl = "{{ url('/') }}";
                const updateBaseUrl = "{{ url('/admin/menu') }}"; // ปรับให้ตรงกับ Route แล้ว

                let menuModal;
                document.addEventListener("DOMContentLoaded", function() {
                    menuModal = new bootstrap.Modal(document.getElementById('mainMenuModal'));
                });

                function openMenuModal(mode, btnElement = null) {
                    const form = document.getElementById('mainMenuForm');
                    const methodInput = document.getElementById('formMethod');
                    const titleText = document.getElementById('modalTitleText');
                    
                    form.reset();
                    document.getElementById('previewImage').src = baseAppUrl + "/img/logo.png";
                    document.getElementById('recipeContainer').innerHTML = '';
                    document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);

                    if (mode === 'create') {
                        titleText.innerText = "เพิ่มข้อมูลสินค้าใหม่";
                        form.action = storeRoute;
                        methodInput.value = "POST";
                        addRecipeRow();

                    } else if (mode === 'edit') {
                        titleText.innerText = "แก้ไขข้อมูลสินค้า";
                        
                        const product = JSON.parse(btnElement.getAttribute('data-product'));
                        const categories = JSON.parse(btnElement.getAttribute('data-categories'));
                        const recipes = JSON.parse(btnElement.getAttribute('data-recipes'));

                        // ชี้ Route ไปที่การ Update (Method PUT)
                        form.action = `${updateBaseUrl}/${product.id}`; 
                        methodInput.value = "PUT";

                        document.getElementById('productName').value = product.name;
                        document.getElementById('productPrice').value = product.price;
                        document.getElementById('productDesc').value = product.description || '';
                        
                        if(product.imgurl) {
                            document.getElementById('previewImage').src = `${baseAppUrl}/${product.imgurl}`;
                        }

                        // ติ๊กหมวดหมู่
                        categories.forEach(catId => {
                            const checkbox = document.querySelector(`.category-checkbox[value="${catId}"]`);
                            if(checkbox) checkbox.checked = true;
                        });

                        // เพิ่มสูตร
                        if(recipes && recipes.length > 0) {
                            recipes.forEach(recipe => addRecipeRow(recipe.ingredient_id, recipe.amount));
                        } else {
                            addRecipeRow();
                        }
                    }

                    menuModal.show();
                    updateIngredientOptions();
                }

                function addRecipeRow(ingredientId = '', amount = '') {
                    const container = document.getElementById('recipeContainer');
                    const template = document.getElementById('recipeTemplate').querySelector('.recipe-row').cloneNode(true);
                    
                    if (ingredientId !== '') template.querySelector('.ingredient-select').value = ingredientId;
                    if (amount !== '') template.querySelector('.amount-input').value = amount;

                    container.appendChild(template);
                    updateIngredientOptions();
                }

                function removeRecipeRow(btn) {
                    btn.closest('.recipe-row').remove();
                    updateIngredientOptions();
                }

                function updateIngredientOptions() {
                    const selects = document.querySelectorAll('#recipeContainer .ingredient-select');
                    const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== '');

                    selects.forEach(select => {
                        Array.from(select.options).forEach(option => {
                            if (option.value !== '' && selectedValues.includes(option.value) && option.value !== select.value) {
                                option.disabled = true;
                            } else {
                                option.disabled = false;
                            }
                        });
                    });
                }

                function previewUploadImage(input) {
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) { document.getElementById('previewImage').src = e.target.result; };
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function createCategoryAjax() {
                    let name = document.getElementById('newCategoryName').value;
                    if (!name) { alert('กรุณากรอกชื่อประเภท'); return; }

                    fetch("{{ route('adminmenu.category.ajaxStore') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ name: name })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            let category = data.category;
                            let html = `
                                <div class="cat-item" data-id="${category.id}" style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                                    <input type="checkbox" name="category_ids[]" value="${category.id}" class="category-checkbox" checked>
                                    <span style="flex:1;">${category.name}</span>
                                    <button type="button" onclick="deleteCategory(${category.id})" class="x-delete-btn">✕</button>
                                </div>`;
                            document.getElementById('categoryList').insertAdjacentHTML('beforeend', html);
                            document.getElementById('newCategoryName').value = '';
                        }
                    });
                }

                function deleteCategory(id) {
                    if (!confirm('ลบประเภทนี้ออกจากระบบถาวร ?')) return;
                    fetch(`/adminmenu/category/ajax-delete/${id}`, {
                        method: "DELETE",
                        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
                    }).then(res => res.json()).then(data => {
                        if (data.success) { document.querySelector(`[data-id='${id}']`).remove(); }
                    });
                }
            </script>
        </x-card>
    </x-grid>
</x-app-layout>