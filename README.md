# ChaNomPos - ระบบบริหารจัดการร้านชานมไข่มุก (POS & Inventory)

ChaNomPos คือเว็บแอปพลิเคชันสำหรับบริหารจัดการร้านชานมไข่มุกและเครื่องดื่ม พัฒนาด้วย Laravel และ Livewire เพื่อช่วยให้การรับออเดอร์ การจัดการสต็อก และการสรุปยอดขายทำได้ง่ายและมีประสิทธิภาพ

---

## คุณสมบัติเด่น (Features)

### 🥤 ระบบขายหน้าร้าน (Point of Sale)
- เลือกรายการเมนูตามหมวดหมู่
- ระบบตะกร้าสินค้า (เพิ่ม/ลดจำนวน, ลบรายการ)
- คำนวณราคารวมและชำระเงิน

### 📋 การจัดการเมนู (Menu Management)
- เพิ่ม แก้ไข และลบรายการเมนู
- จัดการหมวดหมู่สินค้า (Categories)
- เปิด/ปิด การแสดงผลเมนูที่หมดชั่วคราว

### 📦 ระบบจัดการสต็อก (Inventory Control)
- ติดตามปริมาณวัตถุดิบ (Ingredients)
- บันทึกประวัติการเพิ่ม/ลดสต็อก (Stock Logs)
- ระบบแจ้งเตือนหรือแสดงสถานะวัตถุดิบ

### 📊 ประวัติการสั่งซื้อ (Order History)
- ดูรายการขายย้อนหลัง
- ตรวจสอบรายละเอียดในแต่ละออเดอร์
- สำหรับ Admin: สามารถจัดการ (ลบ/แก้ไข) ออเดอร์ได้

### 🔐 ระบบสมาชิกและสิทธิ์การใช้งาน (Auth & Roles)
- **Admin**: จัดการเมนู, สต็อกทั้งหมด, และดูภาพรวมระบบ
- **Staff (User)**: รับออเดอร์หน้าร้านและอัปเดตสต็อกเบื้องต้น
- ระบบ Profile และความปลอดภัยจาก Laravel Jetstream

---

## เทคโนโลยีที่ใช้ (Technologies Used)

### Framework & Language
- **PHP 8.1+**
- **Laravel 10**
- **Livewire 3** (Full-stack framework for dynamic interfaces)

### UI & Frontend
- **Tailwind CSS**
- **Alpine.js**
- **Blade Templates**
- **Vite** (Frontend Build Tool)

### Database & Auth
- **MySQL**
- **Laravel Jetstream** (Authentication, Profile Management)
- **Sanctum** (API Authentication)

---

## โครงสร้างโปรเจกต์ (Project Structure)

```text
ChaNomPos/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminOrderController.php   # จัดการออเดอร์สำหรับ Admin
│   │   ├── MenuController.php         # จัดการรายการเมนูและหมวดหมู่
│   │   ├── SaleController.php         # ระบบ POS และตะกร้าสินค้า
│   │   ├── StockController.php        # จัดการวัตถุดิบและสต็อก
│   │   └── OrderHistoryController.php # ประวัติการสั่งซื้อ
│   └── Models/                        # Database Models (Product, Ingredient, Order, etc.)
├── resources/
│   ├── views/                         # Blade templates (Livewire components)
│   └── js/ & css/                     # Assets (Tailwind, Alpine.js)
├── routes/
│   └── web.php                        # กำหนดเส้นทาง (Routes) ทั้งหมด
├── database/
│   ├── migrations/                    # โครงสร้างฐานข้อมูล
│   └── seeders/                       # ข้อมูลตัวอย่าง (Sample Data)
└── config/                            # ไฟล์ตั้งค่าระบบ
```

---

## การติดตั้ง (Installation)

### 1. Clone repository
```bash
git clone https://github.com/yourusername/ChaNomPos.git
cd ChaNomPos
```

### 2. ติดตั้ง Dependencies
```bash
composer install
npm install
```

### 3. ตั้งค่าสภาพแวดล้อม (Environment Setup)
1. คัดลอกไฟล์ `.env.example` เป็น `.env`
   ```bash
   cp .env.example .env
   ```
2. สร้างฐานข้อมูลใหม่ใน MySQL (เช่น `chanom_pos`)
3. แก้ไขไฟล์ `.env` เพื่อเชื่อมต่อฐานข้อมูล:
   ```env
   DB_DATABASE=chanom_pos
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. สร้าง Application Key:
   ```bash
   php artisan key:generate
   ```

### 4. Migrate และ Seed ข้อมูล
```bash
php artisan migrate --seed
```
*(การใช้ `--seed` จะช่วยสร้างข้อมูลหมวดหมู่และเมนูเริ่มต้น รวมถึงบัญชี Admin ตัวอย่าง)*

### 5. Build Assets และรันระบบ
```bash
npm run dev
# หรือ
npm run build

php artisan serve
```

---

## วิธีใช้งาน (Usage)

1. **เข้าสู่ระบบ**: เข้าไปที่ `http://localhost:8000/login`
2. **หน้าขาย (Dashboard)**: เลือกเมนูที่ต้องการ -> คลิก "เพิ่มลงตะกร้า" -> ตรวจสอบรายการ -> คลิก "ชำระเงิน"
3. **จัดการสต็อก**: สำหรับเจ้าหน้าที่ สามารถเข้าไปอัปเดตจำนวนวัตถุดิบได้ที่เมนู "สต็อก"
4. **จัดการระบบ (Admin)**: เข้าถึงหน้าจัดการเมนู เพื่อเพิ่มรายการเครื่องดื่มใหม่ๆ หรือจัดการหมวดหมู่

---

## สรุปภาพรวมระบบ (System Overview)

| หน้าหลัก (POS) |  | จัดการเมนู |  | สต็อกสินค้า |
|---|---|---|---|---|
| (ใส่รูป Screenshot) |  | (ใส่รูป Screenshot) |  | (ใส่รูป Screenshot) |

---

## ผู้พัฒนา (Developer)
- Your Name / GitHub Profile

---
*โปรเจกต์นี้สร้างขึ้นเพื่อช่วยเพิ่มประสิทธิภาพในการจัดการร้านเครื่องดื่มขนาดเล็กถึงกลาง*
