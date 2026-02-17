<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class MenuController extends Controller
{
    public function adminMenu()
{
    $products = Product::with('recipes.ingredient')->get();
    $ingredients = Ingredient::with('recipe')->get();

    return view('page.adminmenu', compact('products', 'ingredients'));
}

    public function activate($id)
{
    $product = Product::findOrFail($id);
    $product->is_active = 1;
    $product->save();

    return redirect()->back();
    }

    public function create()
{
    $categories = Category::all();
    $ingredients = Ingredient::all();

    return view('page.adminmenu_create', compact('categories', 'ingredients'));
}

    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required',
        'price' => 'required|numeric',
        'description' => 'nullable',
        'image' => 'nullable|image',
        'categories' => 'nullable|array',
        'ingredients' => 'nullable|array',
        'amounts' => 'nullable|array',
    ]);

        // ✅ ตั้ง default รูปก่อน
    $data['imgurl'] = 'img/default.png';

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('img'), $filename);
        $data['imgurl'] = 'img/'.$filename;
    }

    $product = Product::create($data);

    if ($request->categories) {

    $categoryIds = [];

    foreach ($request->categories as $catName) {

        // ข้ามค่าที่ว่าง
        if (!$catName || trim($catName) === '') {
            continue;
        }

        $category = \App\Models\Category::firstOrCreate([
            'name' => trim($catName)
        ]);

        $categoryIds[] = $category->id;
    }

    if (!empty($categoryIds)) {
        $product->categories()->attach($categoryIds);
    }
}

    if ($request->ingredients) {
        foreach ($request->ingredients as $i => $ing) {
            if ($ing && $request->amounts[$i]) {
                $product->recipes()->create([
                    'ingredient_id' => $ing,
                    'amount' => $request->amounts[$i]
                ]);
            }
        }
    }

    return redirect()->route('adminmenu');
}


    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // ลบรูปใน public/img ด้วย (แนะนำให้ลบ)
        if ($product->imgurl && file_exists(public_path($product->imgurl))) {
            unlink(public_path($product->imgurl));
        }
        $product->delete();
        return redirect()->back()->with('success', 'ลบเมนูสำเร็จ');
    }

    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $data = $request->validate([
        'name' => 'required',
        'price' => 'required|numeric',
        'description' => 'nullable',
    ]);
    $product->update($data);

    return redirect()->back();
}

        public function storemodal(Request $request)
        {

$request->validate([
    'product_id' => 'required|exists:products,id',
    'ingredient_id' => [
        'required',
        'exists:ingredients,id',
        Rule::unique('recipes')
            ->where(fn ($query) =>
                $query->where('product_id', $request->product_id)
            )
    ],
    'amount' => 'required|numeric|min:0.01'
], [
    'ingredient_id.unique' => 'วัตถุดิบนี้อยู่ในเมนูนี้แล้ว'
]);

        Recipe::create([
            'product_id' => $request->product_id,
            'ingredient_id' => $request->ingredient_id,
            'amount' => $request->amount
        ]);

        return back()->with('success', 'เพิ่มสูตรสำเร็จ');
    }

    public function destroymodal(Recipe $recipe)
    {
        $recipe->delete();
        return back()->with('success', 'ลบสูตรแล้ว');
    }


    public function detachCategory(Product $product, Category $category)
    {
    $product->categories()->detach($category->id);

    return back();
    }

    public function addCategory(Request $request, Product $product){
    $request->validate([
        'name' => 'required|string|max:255',
    ]);
    // สร้างหรือดึง category เดิม
    $category = Category::firstOrCreate([
        'name' => $request->name,
    ]);
    // ผูกกับ product (กันซ้ำ)
    $product->categories()->syncWithoutDetaching($category->id);
    return back();
    }

    public function updatemodal(Request $request, Product $product)
{
    $request->validate([
        'name' => 'required',
        'price' => 'required|numeric',
        'description' => 'nullable',
    ]);

    $product->update($request->only('name','price','description'));

    return back()->with('success', 'บันทึกเรียบร้อย');
}

public function updateimgmodal(Request $request, Product $product)
{
    $request->validate([
        'image' => 'required|image'
    ]);

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time().'_'.$file->getClientOriginalName();

        $file->move(public_path('img'), $filename);

        // อัปเดต path แค่ครั้งเดียว
        $product->imgurl = 'img/'.$filename;
        $product->save();
    }

    return back()->with('success', 'อัปเดตรูปสำเร็จ');
}
public function toggle(Product $product)
{
    $product->is_active = !$product->is_active;
    $product->save();

    return back();
}



}
