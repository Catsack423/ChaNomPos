<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Real_ingrediant extends Model
{   
    use HasFactory;
    use SoftDeletes;
    protected $table = 'real_ingredients'; 
    protected $fillable = ['ingredient_id', 'quantity','price','expried' ,'in_use'];

    public function logs(){
        return $this->hasMany(InventoryLog::class);
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class);
    }

}
