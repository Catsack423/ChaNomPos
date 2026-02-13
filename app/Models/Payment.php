<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'sale_id','method','amount','paid_at'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

