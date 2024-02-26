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
        Schema::create('displacement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('displacement_id')->nullable()->comment('ID перемещения')->constrained('displacements')->сascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->comment('ID товара')->constrained('order_items')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displacement_items');
    }
};
