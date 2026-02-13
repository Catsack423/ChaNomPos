<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\InventoryLog; // เพิ่ม Model นี้เข้ามา
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้าง Users (ต้องมีคนทำรายการ Log)
        $admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'admin' => true
        ]);

        $staff = User::factory()->count(3)->create();

        // 2. สร้าง Categories
        $categories = Category::factory()->count(5)->create();

        // 3. สร้าง Ingredients และบันทึก Log การนำเข้า (Action: add)
        $ingredients = Ingredient::factory()->count(10)->create()->each(function ($ing) use ($admin) {
            $initialQty = rand(5000, 10000);
            
            Inventory::create([
                'ingredient_id' => $ing->id,
                'quantity' => $initialQty,
                'min_level' => 500,
            ]);

            // บันทึก Log เมื่อมีการเพิ่มสต็อกเริ่มต้น
            InventoryLog::create([
                'ingredient_id' => $ing->id,
                'user_id' => $admin->id,
                'action' => 'add',
                'quantity' => $initialQty,
                'reason' => 'นำเข้าสต็อกเริ่มต้น (System Seed)',
            ]);
        });

        // 4. สร้าง Products พร้อม Recipes
        $products = Product::factory()->count(50)->create()->each(function ($product) use ($categories, $ingredients) {
            $product->categories()->attach($categories->random(rand(1, 2))->pluck('id'));

            // สุ่มสร้างสูตร 2-4 อย่างต่อ 1 เมนู
            foreach ($ingredients->random(rand(2, 4)) as $ing) {
                Recipe::create([
                    'product_id' => $product->id,
                    'ingredient_id' => $ing->id,
                    'amount' => rand(10, 100),
                ]);
            }
        });

        // 5. สร้าง Sales และบันทึก Log การตัดสต็อกตามสูตร (Action: reduce)
        Sale::factory()->count(100)->create()->each(function ($sale) use ($products) {
            $selectedProducts = $products->random(rand(1, 4));
            
            foreach ($selectedProducts as $product) {
                $qtyOrdered = rand(1, 3);
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qtyOrdered,
                    'price' => $product->price,
                ]);

                // --- ส่วนที่เพิ่มมา: ตัดสต็อกตาม Recipe และบันทึก Log ---
                foreach ($product->recipes as $recipe) {
                    $totalUsed = $recipe->amount * $qtyOrdered;

                    InventoryLog::create([
                        'ingredient_id' => $recipe->ingredient_id,
                        'user_id' => $sale->user_id,
                        'action' => 'reduce',
                        'quantity' => $totalUsed,
                        'reason' => "ตัดสต็อกจากการขาย Order #{$sale->id}",
                    ]);

                    // ลดจำนวนในตาราง Inventory จริงๆ
                    Inventory::where('ingredient_id', $recipe->ingredient_id)
                             ->decrement('quantity', $totalUsed);
                }
            }

            // อัปเดตราคารวมบิล
            $sale->update(['total_price' => $sale->items->sum(fn($i) => $i->quantity * $i->price)]);
        });
    }
}