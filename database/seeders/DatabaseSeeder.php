<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Real_ingrediant; // ตรวจสอบว่าใน Model สะกดแบบนี้
use App\Models\InventoryLog;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- 1. สร้าง Users (Admin และ Staff) ---
        $admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'admin' => true
        ]);

        $staffs = User::factory()->count(3)->create(['admin' => false]);

        // --- 2. สร้าง Categories (5 หมวดหมู่) ---
        $categories = Category::factory()->count(5)->create();

        // --- 3. สร้าง Ingredients และ ล็อตสต็อก (Real_ingrediants) ---
        // สร้าง 10 วัตถุดิบ แต่ละอย่างมี 2 ล็อต (ล็อตเก่าที่เปิดใช้ และ ล็อตใหม่ที่สำรองไว้)
        $ingredients = Ingredient::factory()->count(10)->create()->each(function ($ing) use ($admin) {
            
            // ล็อตที่ 1: กำลังใช้งาน (in_use = true)
            $activeStock = Real_ingrediant::create([
                'ingredient_id' => $ing->id,
                'quantity' => 5000,
                'price' => rand(100, 500),
                'expried' => now()->addMonths(6),
                'in_use' => true,
            ]);

            // บันทึก Log การนำเข้าสต็อกก้อนแรก
            InventoryLog::create([
                'real_ingredient_id' => $activeStock->id,
                'user_id' => $admin->id,
                'action' => 'add',
                'quantity' => 5000,
                'reason' => "นำเข้าสต็อกเริ่มต้น ล็อตที่ #{$activeStock->id}",
            ]);

            // ล็อตที่ 2: สำรองไว้ (in_use = false)
            Real_ingrediant::create([
                'ingredient_id' => $ing->id,
                'quantity' => 5000,
                'price' => rand(100, 500),
                'expried' => now()->addMonths(12),
                'in_use' => false,
            ]);
        });

        // --- 4. สร้าง Products พร้อม Recipe ---
        // สร้าง 30 สินค้า แต่ละอย่างสุ่มใช้ 2-3 วัตถุดิบ
        $products = Product::factory()->count(30)->create()->each(function ($product) use ($categories, $ingredients) {
            // สุ่มหมวดหมู่
            $product->categories()->attach($categories->random(rand(1, 2))->pluck('id'));

            // สุ่มวัตถุดิบทำสูตร
            $selectedIngredients = $ingredients->random(rand(2, 3));
            foreach ($selectedIngredients as $ing) {
                Recipe::create([
                    'product_id' => $product->id,
                    'ingredient_id' => $ing->id,
                    'amount' => rand(20, 100), // ปริมาณที่ใช้ต่อหน่วยสินค้า
                ]);
            }
        });

        // --- 5. สร้าง Sales และ Logic การตัดสต็อก ---
        // สร้าง 50 บิลขาย
        Sale::factory()->count(50)->create()->each(function ($sale) use ($products) {
            // สุ่มสินค้า 1-3 อย่างใน 1 บิล
            $items = $products->random(rand(1, 3));

            foreach ($items as $product) {
                $qtyOrdered = rand(1, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qtyOrdered,
                    'price' => $product->price,
                ]);

                // ค้นหาสูตรเพื่อตามไปตัดสต็อกใน Real_ingrediants
                foreach ($product->recipes as $recipe) {
                    $totalUsed = $recipe->amount * $qtyOrdered;

                    // ค้นหาล็อตที่ 'in_use' เป็น true สำหรับวัตถุดิบชิ้นนี้
                    $activeLot = Real_ingrediant::where('ingredient_id', $recipe->ingredient_id)
                        ->where('in_use', true)
                        ->first();

                    if ($activeLot) {
                        // 1. ลดจำนวนในสต็อก
                        $activeLot->decrement('quantity', $totalUsed);

                        // 2. บันทึก Log โดยอ้างอิง real_ingredient_id
                        InventoryLog::create([
                            'real_ingredient_id' => $activeLot->id,
                            'user_id' => $sale->user_id,
                            'action' => 'reduce',
                            'quantity' => $totalUsed,
                            'reason' => "ขาย Order #{$sale->id} (Item: {$product->name})",
                        ]);
                    }
                }
            }

            // อัปเดตราคาทั้งหมดในบิล
            $total = $sale->items->sum(fn($item) => $item->quantity * $item->price);
            $sale->update(['total_price' => $total]);
        });
    }
}