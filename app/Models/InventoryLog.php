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
        'ingredient_id',
        'user_id',
        'action',
        'quantity',
        'reason',
        'created_at' // อนุญาตให้กรอกค่านี้ได้
    ];

    // ถ้าคุณต้องการให้ Laravel บันทึกเฉพาะ created_at ให้อัตโนมัติเมื่อสร้างข้อมูล
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}