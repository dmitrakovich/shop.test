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
        Schema::dropIfExists('product_carousels');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_carousels', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('categories')->nullable();
            $table->boolean('is_imidj')->default(false);
            $table->boolean('only_sale')->default(false);
            $table->boolean('only_new')->default(false);
            $table->unsignedInteger('speed')->default(3000);
            $table->unsignedSmallInteger('count')->default(15);
            $table->unsignedTinyInteger('sorting')->default(0);
            $table->timestamps();
            $table->unsignedTinyInteger('enum_type_id')->nullable();
            $table->json('additional_settings')->nullable()->comment('Дополнительные настройки');
        });
    }
};
