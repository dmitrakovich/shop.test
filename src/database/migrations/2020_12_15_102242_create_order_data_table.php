<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index();
            $table->foreignId('product_id');
            $table->foreignId('size_id');
            $table->integer('count');
            $table->float('buy_price');
            $table->float('price');
            $table->float('old_price');
            $table->float('current_price');
            $table->float('discount')->default(0);
            $table->boolean('promocode_applied')->default(false);
            $table->foreignId('status_id')->default(1);
            $table->date('release_date')->nullable();
            $table->unsignedTinyInteger('pred_period')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_data');
    }
}
