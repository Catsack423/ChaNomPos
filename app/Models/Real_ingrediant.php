<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;



class Real_ingrediant extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'real_ingredients';
    protected $fillable = [
    'name',
    'product_id',
    'ingredient_id',
    'quantity',
    'price',
    'expried',
    'in_use',
    'imgurl'
];

   public function logs()
{
    return $this->hasMany(InventoryLog::class,'real_ingredient_id');
}

    public function ingredient()
{
    return $this->belongsTo(Ingredient::class,'ingredient_id');
}
    protected static function booted()
{
    static::creating(function ($model) {
        $model->created_at = $model->freshTimestamp();
    });

    // ตรวจสอบหลังจากมีการสร้าง Log (เช่น การตัดสต็อกขาย หรือปรับปรุงมือ)
    static::created(function ($log) {
        // ถ้ามีการระบุ real_ingredient_id (เป็น Lot สินค้าจริง)
        if ($log->real_ingredient_id) {
            $lot = $log->real_ingredient;

            // ใช้ฟังก์ชัน remaining() ที่คุณเขียนไว้ใน Model
            // ถ้าเหลือน้อยกว่าหรือเท่ากับ 0 ให้ลบทิ้งทันที
            if ($lot && $lot->remaining() <= 0) {
                $lot->delete(); // นี่คือการทำ Soft Delete เพราะใน Model มี use SoftDeletes
            }
        }
    });
}
public function usedQuantity()
{
    return $this->logs()
        ->where('action','reduce')
        ->sum('quantity');
}

public function remaining()
{
    return $this->quantity - $this->usedQuantity();
}

public function percentRemaining()
{
    if($this->quantity == 0) return 0;

    return round(($this->remaining() / $this->quantity) * 100);
}
public function product()
{
    return $this->belongsTo(Product::class,'product_id');
}
}
