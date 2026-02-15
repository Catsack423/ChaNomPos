<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. สร้าง Users (Admin และ Staff) ---
        // ใช้ is_admin ให้ตรงตาม Migration
        $admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'admin' => true
        ]);

        $staffs = User::factory()->count(3)->create(['admin' => false]);

        // --- 2. สร้าง Categories (5 หมวดหมู่) ---
        $categories = Category::factory()->count(5)->create();

        // --- 3. สร้าง Ingredients และสต็อกเริ่มต้น ---
        // ปั้ม 10 อย่าง และบันทึก Log การนำเข้าครั้งแรก
        $ingredients = Ingredient::factory()->count(10)->create()->each(function ($ing) use ($admin) {
            $initialQty = rand(5000, 10000);
            
            Inventory::create([
                'ingredient_id' => $ing->id,
                'quantity' => $initialQty,
                'min_level' => 500,
            ]);

            InventoryLog::create([
                'ingredient_id' => $ing->id,
                'user_id' => $admin->id,
                'action' => 'add',
                'quantity' => $initialQty,
                'reason' => 'นำเข้าสต็อกเริ่มต้น (System Seed)',
            ]);
        });

        // --- 4. สร้าง Products พร้อม Recipe (1 Product มีหลาย Ingredients) ---
        // ปั้ม 50 รายการ
        $products = Product::factory()->count(50)->create()->each(function ($product) use ($categories, $ingredients) {
            // สุ่มผูกหมวดหมู่ (1 สินค้าอยู่ได้หลายหมวด)
            $product->categories()->attach($categories->random(rand(1, 2))->pluck('id'));

            // สุ่มเลือกวัตถุดิบ 2-4 อย่างมาทำเป็นสูตร (Recipe)
            $selectedIngredients = $ingredients->random(rand(2, 4));

            foreach ($selectedIngredients as $ing) {
                Recipe::create([
                    'product_id' => $product->id,
                    'ingredient_id' => $ing->id,
                    'amount' => rand(10, 100), // ปริมาณที่ใช้ต่อ 1 แก้ว
                ]);
            }
        });

        // --- 5. สร้าง Sales และระบบตัดสต็อกอัตโนมัติ ---
        // ปั้มยอดขาย 100 บิล
        Sale::factory()->count(100)->create()->each(function ($sale) use ($products) {
            // ในแต่ละบิล สุ่มสินค้า 1-4 อย่าง
            $selectedItems = $products->random(rand(1, 4));
            
            foreach ($selectedItems as $product) {
                $qtyOrdered = rand(1, 3);
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qtyOrdered,
                    'price' => $product->price,
                ]);

                // วนลูปตัดสต็อกตามสูตร (Recipe) ของสินค้าชิ้นนั้น
                foreach ($product->recipes as $recipe) {
                    $totalUsed = $recipe->amount * $qtyOrdered;

                    // บันทึกประวัติการลดสต็อก
                    InventoryLog::create([
                        'ingredient_id' => $recipe->ingredient_id,
                        'user_id' => $sale->user_id,
                        'action' => 'reduce',
                        'quantity' => $totalUsed,
                        'reason' => "ตัดสต็อกจากการขาย Order #{$sale->id}",
                    ]);

                    // ลดจำนวนในตารางสต็อกจริง
                    Inventory::where('ingredient_id', $recipe->ingredient_id)
                             ->decrement('quantity', $totalUsed);
                }
            }

            // คำนวณราคารวมของบิลนี้ใหม่ให้ถูกต้อง
            $total = $sale->items->sum(fn($item) => $item->quantity * $item->price);
            $sale->update(['total_price' => $total]);
        });
    }
}