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
    $products = Product::with(['recipes.ingredient','categories'])->get(); // 👈 เพิ่มตรงนี้
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

if ($request->category_ids) {
        $product->categories()->attach($request->category_ids);
    }

    // 🔹 สร้างประเภทใหม่
    if ($request->new_categories) {
        foreach ($request->new_categories as $catName) {
            if ($catName) {
                $category = Category::firstOrCreate(['name' => $catName]);
                $product->categories()->attach($category->id);
            }
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
//ajax
public function ajaxStoreCategory(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255'
    ]);

    $category = Category::create([
        'name' => $request->name
    ]);

    return response()->json([
        'success' => true,
        'category' => $category
    ]);
}


public function ajaxDeleteCategory($id)
{
    $category = Category::findOrFail($id);
    $category->delete();

    return response()->json([
        'success' => true
    ]);
}

//

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


    public function deleteCategory(Category $category)
{
    $category->delete(); // cascade จะลบ pivot ให้เอง
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

    public function syncCategory(Request $request, Product $product)
{
    $product->categories()->sync($request->categories ?? []);
    return back()->with('success', 'อัปเดตประเภทสินค้าแล้ว');
}
public function toggleCategory(Request $request, Product $product)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'checked' => 'required|boolean',
    ]);

    if ($request->checked) {
        $product->categories()->syncWithoutDetaching($request->category_id);
    } else {
        $product->categories()->detach($request->category_id);
    }

    return response()->json(['success' => true]);
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
