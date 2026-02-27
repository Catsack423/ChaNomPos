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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2); // แนะนำให้กำหนด precision
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable(); // เผื่อกรณีไม่มีคำบรรยาย
            $table->string('imgurl')->nullable();
            $table->timestamps();
            
            // เพิ่มบรรทัดนี้เพื่อรองรับ Soft Deletes
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};