<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit'];

    public function inventory(){
        return $this->hasOne(Inventory::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function logs(){
        return $this->hasOne(InventoryLog::class);
    }

}
