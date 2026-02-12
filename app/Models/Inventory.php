<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['ingredient_id','quantity','min_level','updated_at'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
