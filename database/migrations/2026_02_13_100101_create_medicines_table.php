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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Pakai foreignId biasa tanpa constraint dulu agar tidak tergantung urutan migration
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('sku')->unique();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('price', 15, 2);
            $table->string('unit');
            $table->date('expiry_date');
            $table->boolean('is_prescription_required')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
