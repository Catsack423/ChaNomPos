<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;


class MenuController extends Controller
{
    public function adminMenu()
{
    $products = Product::all();
    return view('page.adminmenu', compact('products'));
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
    return view('page.adminmenu_create');
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'imgurl' => 'required|image',
        ]);

        $image = $request->file('imgurl');
        $filename = time().'_'.$image->getClientOriginalName();
        $image->move(public_path('img'), $filename);

        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'imgurl' => 'img/'.$filename,
            'is_active' => 0,
        ]);
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
        'imgurl' => 'nullable|image',
    ]);

    if ($request->hasFile('imgurl')) {
        $image = $request->file('imgurl');
        $filename = time().'_'.$image->getClientOriginalName();
        $image->move(public_path('img'), $filename);
        $data['imgurl'] = 'img/'.$filename;
    }

    $product->update($data);

    return redirect()->back();
}

}
