<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs'; // ระบุชื่อตารางให้ชัดเจน

    // ปิดการใช้ timestamps แบบมาตรฐาน (เพราะเราไม่มี updated_at)
    public $timestamps = false;

    protected $fillable = [
        'real_ingredient_id',
        'user_id',
        'action',
        'quantity',
        'reason',
        'created_at' // อนุญาตให้กรอกค่านี้ได้
    ];

    // ถ้าคุณต้องการให้ Laravel บันทึกเฉพาะ created_at ให้อัตโนมัติเมื่อสร้างข้อมูล
   protected static function booted()
{
    static::created(function ($log) {
        // ตรวจสอบว่ามี Lot id หรือไม่
        if ($log->real_ingredient_id) {
            $lot = $log->real_ingredient;
            // ถ้าคำนวณ remaining() แล้วได้ 0 หรือน้อยกว่า ให้ลบจริง (SoftDelete)
            if ($lot && $lot->remaining() <= 0) {
                $lot->delete();
            }
        }
    });
}

    public function ingredient()
{
    return $this->belongsTo(Ingredient::class,'ingredient_id');
}

    public function real_ingredient()
{
    return $this->belongsTo(Real_ingrediant::class,'real_ingredient_id');
}

    public function user()
{
    return $this->belongsTo(User::class,'user_id');
}
}
