<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            // เชื่อมไปหา Product
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // เชื่อมไปหา Category
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

    }
};
