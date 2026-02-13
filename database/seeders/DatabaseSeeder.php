<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\InventoryLog;
use App\Models\Recipe;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ล้างข้อมูลเก่า
        SaleItem::truncate();
        Payment::truncate();
        Sale::truncate();
        InventoryLog::truncate();
        Recipe::truncate();
        Inventory::truncate();
        Product::truncate();
        Ingredient::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // -------------------------------
        // 1) Users
        // -------------------------------
        $users = User::factory()->count(5)->create();

        // -------------------------------
        // 2) Products (50 รายการ)
        // -------------------------------
        $products = Product::factory()
            ->count(50)
            ->create();

        // -------------------------------
        // 3) Ingredients (10 อย่าง)
        // -------------------------------
        $ingredients = Ingredient::factory()
            ->count(10)
            ->create();

        // -------------------------------
        // 4) Inventory Stock ของ Ingredient
        // -------------------------------
        $ingredients->each(function ($ingredient) {
            Inventory::create([
                'ingredient_id' => $ingredient->id,
                'quantity'      => rand(50, 200),
                'min_level'     => 20,
                'updated_at'    => now()
            ]);
        });

        // -------------------------------
        // 5) Recipe (Product ใช้วัตถุดิบ)
        // สุ่ม 2-4 ingredients ต่อสินค้า
        // -------------------------------
        $products->each(function ($product) use ($ingredients) {

            $usedIngredients = $ingredients->random(rand(2, 4));

            foreach ($usedIngredients as $ing) {
                Recipe::create([
                    'product_id'    => $product->id,
                    'ingredient_id' => $ing->id,
                    'amount'        => rand(1, 5)
                ]);
            }
        });

        // -------------------------------
        // 6) Sales 100 บิล
        // -------------------------------
        Sale::factory()
            ->count(100)
            ->create([
                'user_id' => $users->random()->id
            ])
            ->each(function ($sale) use ($products, $users) {

                // สุ่มสินค้า 1-5 ชิ้นต่อบิล
                $items = $products->random(rand(1, 5));

                foreach ($items as $item) {

                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $item->id,
                        'quantity'   => rand(1, 3),
                        'price'      => $item->price,
                    ]);
                }

                // -------------------------------
                // Update total_price อัตโนมัติ
                // -------------------------------
                $sale->update([
                    'total_price' => $sale->items()
                        ->sum(DB::raw('quantity * price'))
                ]);

                // -------------------------------
                // 7) Payment (1 payment ต่อ sale)
                // -------------------------------
                Payment::create([
                    'sale_id' => $sale->id,
                    'method'  => collect(['cash', 'qr', 'credit'])->random(),
                    'amount'  => $sale->total_price,
                    'paid_at' => now()
                ]);

                // -------------------------------
                // 8) Inventory Logs (ลด stock)
                // -------------------------------
                InventoryLog::create([
                    'ingredient_id' => Ingredient::inRandomOrder()->first()->id,
                    'user_id'       => $users->random()->id,
                    'action'        => 'reduce',
                    'quantity'      => rand(1, 5),
                    'reason'        => 'ขายสินค้าในบิล #' . $sale->id,
                    'created_at'    => now()
                ]);
            });
    }
}
