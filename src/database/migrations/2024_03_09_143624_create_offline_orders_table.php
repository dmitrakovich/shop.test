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
        Schema::create('offline_orders', function (Blueprint $table) {
            $table->id();
            $table->char('receipt_number', 10)->unique()->comment('Receipt number');
            $table->foreignId('stock_id')->nullable()->index();
            $table->foreignId('product_id')->nullable()->index();
            $table->foreignId('size_id')->nullable()->index();
            $table->float('price')->comment('Цена покупки');
            $table->unsignedTinyInteger('count')->comment('Number of items in the order');
            $table->foreignId('user_id')->nullable()->index();
            $table->string('user_phone', 20)->index();
            $table->timestamp('sold_at')->comment('Date and time of sale');
            $table->timestamp('returned_at')->comment('Date and time of return');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_orders');
    }
};
