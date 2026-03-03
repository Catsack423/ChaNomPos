 <link rel="preload" as="image" href="{{ asset('img/logo.png') }}" fetchpriority="high">
 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="{{ asset('css/adminmenu.css') }}">
 <link rel="stylesheet" href="{{ asset('css/adminmenumodal.css') }}">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />
 

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

             <div class="menu-header" style="">
                 <h2>รายชื่อสินค้า</h2>
                 <button onclick="openMenuModal('create')" class="btn-add-menu">＋ เพิ่มเมนู</button>
             </div>
             <div class="menu-wrapper">


                 {{-- Grid เมนู --}}
                 <div class="menu-grid-small">
                     @foreach ($products as $product)
                         <div class="menu-card-small" style="">
                             {{-- ปุ่มลบ --}}


                             {{-- รูป --}}
                             <div class="menu-img-box">
                                 @if ($product->imgurl)
                                     <img src="{{ !empty($product->imgurl) && file_exists(public_path($product->imgurl))
                                         ? asset($product->imgurl)
                                         : asset('img/CV-milk-tea.png') }}"
                                         alt="ไม่พบรูปภาพในฐานข้อมูล">
                                 @endif

                             </div>

                             {{-- ข้อมูล --}}
                             <div class="menu-text">
                                 <div>ชื่อ: {{ $product->name }}</div>
                                 <div>ราคา {{ $product->price }} บาท</div>
                                 <div>
                                     @if ($product->is_active)
                                         <span class="badge open">
                                             <span class="dot"></span>
                                             มีสินค้า
                                         </span>
                                     @else
                                         <span class="badge closed">
                                             <span class="dot"></span>
                                             วัตถุดิบไม่พอ
                                         </span>
                                     @endif

                                 </div>
                             </div>

                             {{-- ปุ่มล่าง --}}
                             <div class="menu-footer">
                                 <label class="switch">
                                     <input type="checkbox"
                                         onchange="toggleShow('{{ route('adminmenu.toggle', $product->id) }}', {{ $product->id }}, this)"
                                         {{ $product->is_show ? 'checked' : '' }}>
                                     <span class="slider round"></span>
                                 </label>

                                 {{-- กลุ่มปลุ่มล่างขวาของเมนู --}}
                                 <div class="bottom-right-group">
                                     <button type="button" class="editpum" data-product="{{ json_encode($product) }}"
                                         data-categories="{{ json_encode($product->categories->pluck('id')) }}"
                                         data-recipes="{{ json_encode($product->recipes) }}"
                                         onclick="openMenuModal('edit', this)">
                                         แก้ไขเมนู
                                     </button>
                                     {{-- ฟอร์มลบ: ใส่ id เพื่อให้อ้างอิงได้ง่าย --}}
                                     <form id="delete-form-{{ $product->id }}"
                                         action="{{ route('adminmenu.destroy', $product->id) }}" method="POST"
                                         class="d-none">
                                         @csrf
                                         @method('DELETE')
                                     </form>

                                     {{-- ปุ่มลบ: เรียกฟังก์ชัน JS แทนการ submit ตรงๆ --}}
                                     <button type="button" class="btn-delete"
                                         onclick="confirmDelete('{{ $product->id }}', '{{ $product->name }}')">
                                         ลบ
                                     </button>
                                 </div>
                                 {{-- ปุ่มแก้ไข ดึงข้อมูลใส่ data-* attributes --}}

                             </div>
                         </div>
                     @endforeach
                 </div>
             </div>


             {{-- ================= MODAL หลักอันเดียว ================= --}}
             <div class="modal fade" id="mainMenuModal" tabindex="-1">
                 <div class="modal-dialog modal-xl modal-dialog-centered">
                     <div class="modal-content recipe-modal">

                         <button type="button" class="btn-close modal-close" data-bs-dismiss="modal"></button>

                         <form id="mainMenuForm" method="POST" action="" enctype="multipart/form-data"
                             class="create-form">
                             @csrf
                             <input type="hidden" name="_method" id="formMethod" value="POST">

                             <div class="recipe-modal-body">
                                 {{-- LEFT --}}
                                 <div class="recipe-left">
                                     <label class="image-upload">
                                         <img id="previewImage" src=""
                                             onerror="this.onerror=null; this.src='{{ asset('img/CV-milk-tea.png') }}';"
                                             alt="preview">

                                         <span class="choose-image-text">เลือกรูป</span>
                                         <input type="file" name="image" id="imageInput" accept="image/*" hidden
                                             onchange="previewUploadImage(this)">
                                     </label>

                                     <div class="category-box">
                                         <h6 class="section-title">ประเภทสินค้า</h6>
                                         <div id="categoryContainer">
                                             <div style="display:flex; gap:10px; align-items: center;">
                                                 <input type="text" id="newCategoryName"
                                                     placeholder="เพิ่มประเภทใหม่" class="form-input">
                                                 <button type="button" class="btn-add" onclick="createCategoryAjax()">+
                                                     เพิ่ม</button>
                                             </div>
                                             <div id="categoryList">
                                                 @foreach ($categories as $category)
                                                     <div class="cat-item" data-id="{{ $category->id }}"
                                                         style="display:flex; align-items:center; gap:8px; margin-bottom:8px; margin-top:8px;">
                                                         <input type="checkbox" name="category_ids[]"
                                                             value="{{ $category->id }}" class="category-checkbox">
                                                         <span style="flex:1;">{{ $category->name }}</span>
                                                         <button type="button"
                                                             onclick="deleteCategory({{ $category->id }},'{{ $category->name }}')"
                                                             class="x-delete-btn">✕</button>
                                                     </div>
                                                 @endforeach
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 {{-- RIGHT --}}
                                 <div class="recipe-right">
                                     <h5 class="section-title" id="modalTitleText">ข้อมูลสินค้า</h5>

                                     <input type="text" name="name" id="productName" placeholder="ชื่อเมนู"
                                         required class="form-input">
                                     <input type="number" step="0.01" name="price" id="productPrice"
                                         placeholder="ราคา" required class="form-input">
                                     <textarea name="description" id="productDesc" placeholder="รายละเอียด" required class="form-input"></textarea>

                                     <hr>

                                     <h5 class="section-title" style="margin: 10px">สูตรสินค้า</h5>
                                     <div id="recipeContainer"></div>
                                     <button type="button" class="btn-add" onclick="addRecipeRow()"
                                         style="margin-top: 10px; display: block;">+ เพิ่มวัตถุดิบ</button>
                                 </div>
                             </div>
                             <div class="row-bottom"
                                 style="display: flex; justify-content: center; align-items: center; width: 100%;">
                                 <button type="submit" class="save-btn">บันทึก</button>
                             </div>

                         </form>
                     </div>
                 </div>
             </div>

             {{-- Template สำหรับ Select วัตถุดิบ --}}
             <div id="recipeTemplate" style="display: none;">
                 <div class="recipe-form recipe-row">
                     <select name="ingredients[]" class="form-select ingredient-select"
                         onchange="updateIngredientOptions()">
                         <option value="">-- เลือกวัตถุดิบ --</option>
                         @foreach ($ingredients as $ing)
                             <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                         @endforeach
                     </select>
                     <input type="number" name="amounts[]" placeholder="จำนวน" class="form-input amount-input"
                         step="0.01">
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

                         if (product.imgurl && product.imgurl.trim() !== "") {
                             let fullUrl = `${baseAppUrl}/${product.imgurl}`;
                             let imgElement = document.getElementById('previewImage');

                             imgElement.src = fullUrl;
                             console.log("พบรูป: " + fullUrl);

                             // กันไว้ดีกว่าแก้: ถ้าโหลดรูปตาม URL แล้วพัง (เช่น ไฟล์หาย) ให้สลับไปใช้รูป Default
                             imgElement.onerror = function() {
                                 this.src = `${baseAppUrl}/img/CV-milk-tea.png`;
                                 this.onerror = null; // ป้องกันการวนลูปถ้ารูป Default ก็หายเหมือนกัน
                             };
                         } else {
                             document.getElementById('previewImage').src = `${baseAppUrl}/img/CV-milk-tea.png`;
                         }
                         // ติ๊กหมวดหมู่
                         categories.forEach(catId => {
                             const checkbox = document.querySelector(`.category-checkbox[value="${catId}"]`);
                             if (checkbox) checkbox.checked = true;
                         });

                         // เพิ่มสูตร
                         if (recipes && recipes.length > 0) {
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
                             if (option.value !== '' && selectedValues.includes(option.value) && option.value !==
                                 select.value) {
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
                         reader.onload = function(e) {
                             document.getElementById('previewImage').src = e.target.result;
                         };
                         reader.readAsDataURL(input.files[0]);
                     }
                 }

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
                         body: JSON.stringify({
                             name: name
                         })
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

                 function deleteCategory(id, name) {
                     Swal.fire({
                         // ใช้ <br> และ <span> เพื่อปรับขนาดฟอนต์ของชื่อประเภทให้เล็กลงตามที่คุณต้องการ
                         title: `คุณต้องการลบประเภทสินค้า<br><span style="font-size: 18px; color: #7b4a2e;">"${name}"</span><br>ออกจากระบบถาวรหรือไม่?`,
                         icon: 'warning',
                         showCancelButton: true,
                         confirmButtonColor: '#d33', // สีน้ำตาลธีม Pos ChaNom
                         cancelButtonColor: '#4CAF50 ',
                         confirmButtonText: 'OK',
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
                             // หากกดยืนยัน (OK) จึงจะทำการ Fetch เพื่อลบข้อมูล
                             fetch(`/adminmenu/category/ajax-delete/${id}`, {
                                     method: "DELETE",
                                     headers: {
                                         "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                         "Accept": "application/json"
                                     }
                                 })
                                 .then(res => res.json())
                                 .then(data => {
                                     if (data.success) {
                                         // ลบ Element ออกจากหน้าจอ
                                         const item = document.querySelector(`[data-id='${id}']`);
                                         if (item) item.remove();

                                         // แสดงแจ้งเตือนสำเร็จกึ่งกลางหน้าจอ
                                         Swal.fire({
                                             icon: 'success',
                                             title: 'สำเร็จ!',
                                             text: 'ลบประเภทสินค้าเรียบร้อยแล้ว',
                                             confirmButtonColor: '#7b4a2e',
                                             timer: 1500
                                         });
                                     }
                                 })
                                 .catch(err => {
                                     Swal.fire('ผิดพลาด', 'ไม่สามารถลบข้อมูลได้ โปรดลองอีกครั้ง', 'error');
                                 });
                         }
                     });
                 }

                 const csrf = document.querySelector('meta[name="csrf-token"]').content;

                 function toggleShow(url, id, checkbox) {
                     // 1. ดึง Token แบบปลอดภัย (เช็คก่อนว่ามีไหม)
                     const csrfElement = document.querySelector('meta[name="csrf-token"]');

                     if (!csrfElement) {
                         console.error("Error: ไม่พบ <meta name='csrf-token'> ในส่วน <head> ของ HTML");
                         Swal.fire('ผิดพลาด', 'ไม่พบ CSRF Token ในระบบ', 'error');
                         checkbox.checked = !checkbox.checked; // คืนค่าปุ่ม
                         return;
                     }

                     const csrfToken = csrfElement.content;
                     const originalStatus = !checkbox.checked;

                     console.log("กำลังส่งคำขอไปที่:", url); // สำหรับ Debug

                     fetch(url, {
                             method: "PATCH",
                             headers: {
                                 "Content-Type": "application/json",
                                 "X-CSRF-TOKEN": csrfToken,
                                 "Accept": "application/json"
                             },
                             body: JSON.stringify({
                                 product_id: id
                             })
                         })
                         .then(async res => {
                             const data = await res.json();
                             console.log("Response จากเซิร์ฟเวอร์:", data);

                             if (!res.ok) throw new Error(data.message || 'Server returned error');

                             // แสดง Toast เมื่อสำเร็จ
                             Swal.mixin({
                                 toast: true,
                                 position: 'top-end',
                                 showConfirmButton: false,
                                 timer: 1100,
                                 timerProgressBar: true
                             }).fire({
                                 icon: 'success',
                                 title: data.message
                             });
                             const Toast = Swal.mixin({

                             });


                         })
                         .catch(err => {
                             console.error("เกิดข้อผิดพลาดในการ Fetch:", err);
                             checkbox.checked = originalStatus; // คืนค่าปุ่ม
                             Swal.fire('ผิดพลาด', err.message || 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
                         });
                 }

                 function confirmDelete(id, name) {
                     Swal.fire({
                         title: `คุณต้องการลบ<br><span style="font-size: 18px; color: #7b4a2e; ">"${name}"</span><br>ใช่หรือไม่?`,
                         icon: 'warning',
                         scrollbarPadding: false, // ป้องกันการเพิ่ม padding ที่ทำให้ Navbar ขยับ
                         showCancelButton: true,
                         confirmButtonColor: '#d33',
                         cancelButtonColor: '#4CAF50 ',
                         confirmButtonText: 'ลบ',
                         cancelButtonText: 'ยกเลิก',
                         reverseButtons: true,
                         customClass: {
                             popup: 'swal-small-popup',
                             title: 'swal-small-title',
                             confirmButton: 'swal-small-button',
                             cancelButton: 'swal-small-button'
                         }
                     }).then((result) => {
                         if (result.isConfirmed) {
                             document.getElementById('delete-form-' + id).submit();
                         }
                     });
                 }
             </script>
         </x-card>
     </x-grid>
 </x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js" defer></script>