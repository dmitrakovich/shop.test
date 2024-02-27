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
        Schema::create('displacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_from')->nullable()->comment('Направление откуда')->constrained('stocks')->nullOnDelete();
            $table->foreignId('direction_to')->nullable()->comment('Направление куда')->constrained('stocks')->nullOnDelete();
            $table->timestamp('dispatch_date')->nullable()->comment('Дата отправки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displacements');
    }
};
