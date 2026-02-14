<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'is_active',
        'descripsion',
        'imgurl',
        'category_id' 
    ];

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
