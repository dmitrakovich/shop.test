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

            $table->string('slug', 100)->unique();
            $table->string('sku')->index(); // stock keeping unit
            $table->unsignedInteger('label_id')->default(0);

            $table->decimal('buy_price')->default(0);
            $table->decimal('price')->default(0);
            $table->decimal('old_price')->default(0);
            // $table->decimal('discount')->default(0); // для сортировки (надо высчитывать при добавлении товара)

            $table->foreignId('category_id')->index()->default(0);
            $table->foreignId('season_id')->index()->default(0);
            $table->foreignId('brand_id')->index()->default(0);
            $table->foreignId('manufacturer_id')->index()->default(0);
            $table->foreignId('collection_id')->index()->default(0);

            $table->string('color_txt')->nullable();
            $table->string('fabric_top_txt')->nullable();
            $table->string('fabric_inner_txt')->nullable();
            $table->string('fabric_insole_txt')->nullable();
            $table->string('fabric_outsole_txt')->nullable();
            $table->string('heel_txt')->nullable();
            $table->string('bootleg_height_txt')->nullable();
            $table->text('description')->nullable();

            $table->boolean('action')->default(false);
            $table->unsignedInteger('rating')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['price', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['rating', 'id']);
            // $table->index(['discount', 'id']);
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
