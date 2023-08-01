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
        Schema::create('log_order_item_inventory_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique();
            $table->foreignId('stock_id')->index();
            $table->timestamp('created_at');
            $table->timestamp('sended_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_order_item_inventory_notifications');
    }
};
