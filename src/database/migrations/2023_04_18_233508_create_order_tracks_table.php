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
        Schema::create('order_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('track_number', 128)->nullable()->comment('Трек номер заказа');
            $table->string('track_link')->nullable()->comment('Ссылка для отслеживания трек номера');
            $table->foreignId('order_id')->nullable()->comment('Номер заказа')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->smallInteger('delivery_type_enum')->unsigned()->default(1)->comment('Тип доставки');
            $table->unique(['track_number', 'delivery_type_enum']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tracks');
    }
};
