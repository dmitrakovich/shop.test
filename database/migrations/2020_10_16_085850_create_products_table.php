<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->boolean('publish');

            $table->string('slug', 100)->unique();
            $table->string('title')->index();

            $table->unsignedDecimal('buy_price');
            $table->unsignedDecimal('price');
            $table->unsignedDecimal('old_price');

            $table->foreignId('category_id')->index();
            $table->foreignId('season_id')->index();
            $table->foreignId('color_id')->index();
            $table->foreignId('brand_id')->index();

            $table->string('color_txt')->nullable();
            $table->string('fabric_top_txt')->nullable();
            $table->string('fabric_inner_txt')->nullable();
            $table->string('fabric_insole_txt')->nullable();
            $table->string('fabric_outsole_txt')->nullable();
            $table->string('heel_txt')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
