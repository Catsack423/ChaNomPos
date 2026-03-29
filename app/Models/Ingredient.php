<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit'];

    public function real_ingredients()
{
    return $this->hasMany(Real_ingrediant::class,'ingredient_id');
}

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function logs(){
        return $this->hasManyThrough(InventoryLog::class, Real_ingrediant::class, 'ingredient_id', 'real_ingredient_id');
    }
    public function inventory()
{
    // สมมติว่า 1 วัตถุดิบมี 1 แถวในตาราง inventories
    return $this->hasOne(Inventory::class);
}
public function totalQuantity()
{
    // รวมผลลัพธ์จากฟังก์ชัน remaining() ของทุกล็อตที่ยังไม่ถูก Soft Delete
    return $this->real_ingredient->sum(function($lot) {
        return $lot->remaining();
    });
}
}
