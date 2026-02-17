<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Sale extends Model
{
    use SoftDeletes;
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id','total_price','sold_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function getTotalAttribute()
    {
        return $this->items->sum(fn($item) => 
            $item->price * $item->quantity
        );
    }

}
