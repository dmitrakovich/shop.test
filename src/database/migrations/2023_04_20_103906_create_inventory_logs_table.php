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
        Schema::create('log_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->enum('action', ['RESTORE', 'UPDATE', 'DELETE'])->default('UPDATE');
            $table->json('added_sizes')->nullable();
            $table->json('removed_sizes')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_inventories');
    }
};
