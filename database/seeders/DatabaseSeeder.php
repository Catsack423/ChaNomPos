<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้าง Categories (5 หมวดหมู่)
        $categories = Category::factory()->count(5)->create();

        // 2. สร้าง Ingredients (วัตถุดิบ 10 อย่าง) และใส่ Stock เบื้องต้น
        $ingredients = Ingredient::factory()->count(10)->create()->each(function ($ing) {
            Inventory::create([
                'ingredient_id' => $ing->id,
                'quantity' => rand(2000, 5000),
                'min_level' => 500,
                'updated_at' => now(),
            ]);
        });

        // 3. ปั้ม Products 50 รายการ พร้อม "สูตร" และ "หมวดหมู่"
        $products = Product::factory()->count(50)->create()->each(function ($product) use ($categories, $ingredients) {
            // สุ่มผูกกับ Category 1-2 อัน
            $product->categories()->attach(
                $categories->random(rand(1, 2))->pluck('id')->toArray()
            );

            // --- เพิ่มการสร้าง Recipe (สูตร) ตรงนี้ ---
            // สุ่มวัตถุดิบ 2-4 อย่างมาทำเป็นสูตรสำหรับสินค้าชิ้นนี้
            $randomIngredients = $ingredients->random(rand(2, 4));
            
            foreach ($randomIngredients as $ing) {
                Recipe::create([
                    'product_id'    => $product->id,
                    'ingredient_id' => $ing->id,
                    'amount'        => rand(10, 150), // สุ่มปริมาณที่ต้องใช้ เช่น 10g หรือ 150ml
                ]);
            }
        });

        // 4. ปั้มประวัติการขาย 100 บิล (Sales)
        Sale::factory()->count(100)->create()->each(function ($sale) use ($products) {
            // ในแต่ละบิล สุ่มสินค้า 1-5 อย่าง
            $selectedProducts = $products->random(rand(1, 5));
            
            foreach ($selectedProducts as $product) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => rand(1, 3),
                    'price'      => $product->price,
                ]);
            }

            // อัปเดตราคารวม (ใช้การคำนวณผ่าน PHP เพื่อเลี่ยงปัญหา DB::raw)
            $total = $sale->items->sum(function($item) {
                return $item->quantity * $item->price;
            });
            
            $sale->update(['total_price' => $total]);
        });
    }
}