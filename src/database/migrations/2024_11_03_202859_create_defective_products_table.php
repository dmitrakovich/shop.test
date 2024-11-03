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
        Schema::create('defective_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->comment('id товара')->constrained('products')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('size_id')->comment('id размера')->constrained('sizes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('reason')->nullable()->comment('Причина добавления в брак');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defective_products');
    }
};
