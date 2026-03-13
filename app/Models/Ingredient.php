<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit'];

    public function real_ingredient(){
        return $this->hasMany(Real_ingrediant::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function logs(){
        return $this->hasManyThrough(InventoryLog::class, Real_ingrediant::class, 'ingredient_id', 'real_ingredient_id');
    }

}
