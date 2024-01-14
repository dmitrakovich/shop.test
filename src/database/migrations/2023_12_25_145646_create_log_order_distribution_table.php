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
        Schema::create('log_order_distribution', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->comment('ID заказа');
            $table->unsignedInteger('admin_user_id')->nullable()->comment('ID admin');
            $table->text('action')->nullable()->comment('Действие');
            $table->timestamps();

            $table->foreign('admin_user_id')->references('id')->on('admin_users')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_order_distribution');
    }
};
