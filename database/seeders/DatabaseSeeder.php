<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Sale;
use App\Models\SaleItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้าง Categories เตรียมไว้ก่อน
        $categories = Category::factory()->count(5)->create();

        // 2. ปั้ม Products 50 รายการ
        // แล้วสั่งให้แต่ละอันไป "สุ่ม" ผูกกับ Category 1-2 อัน
        $products = Product::factory()->count(50)->create()->each(function ($product) use ($categories) {
            $product->categories()->attach(
                $categories->random(rand(1, 2))->pluck('id')->toArray()
            );
        });

        // 3. ปั้มวัตถุดิบ 10 อย่าง
        $ingredients = Ingredient::factory()->count(10)->create();

        // 4. ปั้มประวัติการขาย 100 บิล
        Sale::factory()->count(100)->create()->each(function ($sale) use ($products) {
            // ในแต่ละบิล ให้สุ่มสินค้า 1-5 ชิ้น
            $items = $products->random(rand(1, 5));

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item->id,
                    'quantity' => rand(1, 3),
                    'price' => $item->price,
                ]);
            }

            // อัปเดตราคารวมของบิลนั้นๆ
            // อัปเดตราคารวมของบิลนั้นๆ
            $sale->update([
                // เพิ่มวงเล็บ () หลัง items เพื่อเรียก Query Builder
                'total_price' => $sale->items()->sum(DB::raw('quantity * price'))
            ]);
        });
    }
}
