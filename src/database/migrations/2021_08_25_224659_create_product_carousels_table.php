<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCarouselsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_carousels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->index();
            $table->boolean('only_sale')->default(false);
            $table->boolean('only_new')->default(false);
            $table->unsignedSmallInteger('count')->default(15);
            $table->unsignedTinyInteger('sorting')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_carousels');
    }
}
