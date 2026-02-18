<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MenuController extends Controller
{
    public function adminMenu()
    {
        $products = Product::with(['recipes.ingredient', 'categories'])->get();
        $ingredients = Ingredient::with('recipe')->get();
        $categories = Category::all();
        return view('page.adminmenu', compact('products', 'ingredients', 'categories'));
    }

    public function activate($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = 1;
        $product->save();

        return redirect()->back();
    }

    public function toggle(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return back();
    }

    // ================= โหมดสร้างสินค้า (Create) =================
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'image' => 'nullable|image',
            'category_ids' => 'nullable|array',
            'ingredients' => 'nullable|array',
            'amounts' => 'nullable|array',
        ]);

        $data['imgurl'] = 'img/default.png';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img'), $filename);
            $data['imgurl'] = 'img/' . $filename;
        }

        // 1. สร้างสินค้า
        $product = Product::create($data);

        // 2. ซิงค์ประเภทสินค้า
        if ($request->has('category_ids')) {
            $product->categories()->sync($request->category_ids);
        }

        // 3. บันทึกสูตรสินค้า
        if ($request->has('ingredients') && $request->has('amounts')) {
            foreach ($request->ingredients as $index => $ingredient_id) {
                if ($ingredient_id && !empty($request->amounts[$index])) {
                    $product->recipes()->create([
                        'ingredient_id' => $ingredient_id,
                        'amount' => $request->amounts[$index]
                    ]);
                }
            }
        }

        return redirect()->route('adminmenu')->with('success', 'เพิ่มเมนูเรียบร้อยแล้ว');
    }

    // ================= โหมดแก้ไขสินค้า (Update) =================
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'image' => 'nullable|image',
            'category_ids' => 'nullable|array',
            'ingredients' => 'nullable|array',
            'amounts' => 'nullable|array',
        ]);

        // จัดการรูปภาพ (ถ้ามีการอัปโหลดใหม่)
        if ($request->hasFile('image')) {
            // ลบรูปเก่า (ถ้าไม่ใช่ default)
            if ($product->imgurl && $product->imgurl !== 'img/default.png' && File::exists(public_path($product->imgurl))) {
                File::delete(public_path($product->imgurl));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img'), $filename);
            $data['imgurl'] = 'img/' . $filename;
        }

        // 1. อัปเดตข้อมูลพื้นฐาน
        $product->update($data);

        // 2. ซิงค์ประเภทสินค้า (อันไหนไม่ถูกติ๊กจะถูกถอดออกอัตโนมัติ)
        $product->categories()->sync($request->category_ids ?? []);

        // 3. อัปเดตสูตร (ลบของเดิมทิ้งให้หมด แล้วใส่เข้าไปใหม่ตามที่ส่งมา)
        $product->recipes()->delete();
        if ($request->has('ingredients') && $request->has('amounts')) {
            foreach ($request->ingredients as $index => $ingredient_id) {
                if (!empty($ingredient_id) && !empty($request->amounts[$index])) {
                    $product->recipes()->create([
                        'ingredient_id' => $ingredient_id,
                        'amount' => $request->amounts[$index]
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'อัปเดตเมนูเรียบร้อยแล้ว');
    }

    // ================= โหมดลบสินค้า (Destroy) =================
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->imgurl && $product->imgurl !== 'img/default.png' && File::exists(public_path($product->imgurl))) {
            File::delete(public_path($product->imgurl));
        }
        
        $product->delete();
        return redirect()->back()->with('success', 'ลบเมนูสำเร็จ');
    }

    // ================= AJAX Categories =================
    public function ajaxStoreCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = Category::create(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function ajaxDeleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['success' => true]);
    }
}