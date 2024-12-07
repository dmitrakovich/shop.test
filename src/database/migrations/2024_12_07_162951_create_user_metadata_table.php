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
        Schema::create('user_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('last_order_type')->nullable()->comment('Тип последнего заказа');
            $table->dateTime('last_order_date')->nullable()->comment('Дата последнего заказа');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_online_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_metadata');

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_online_orders')->default(false)->after('birth_date');
        });
    }
};
