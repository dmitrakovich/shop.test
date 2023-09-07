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
        Schema::create('log_order_item_pickup_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique();
            $table->boolean('moved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_order_item_pickup_statuses');
    }
};
