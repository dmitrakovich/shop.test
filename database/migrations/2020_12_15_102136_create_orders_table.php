<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('user_name', 200);
            $table->foreignId('user_id')->nullable()->index();
            $table->foreignId('promocode_id')->nullable()->index();
            $table->enum('type', ['retail', 'wholesale']);
            $table->string('email', 50)->nullable();
            $table->string('phone', 20);
            $table->text('comment')->nullable();
            $table->string('currency', 20);
            $table->float('rate');

            $table->string('country', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('street')->nullable();
            $table->string('house', 30)->nullable();
            $table->string('user_addr')->nullable();

            $table->string('payment')->nullable();
            $table->string('payment_code', 30)->nullable();
            $table->float('payment_cost')->nullable();

            $table->string('delivery')->nullable();
            $table->string('delivery_code', 30)->nullable();
            $table->float('delivery_cost')->nullable();
            $table->string('delivery_point')->nullable();
            $table->string('delivery_point_code', 30)->nullable();

            $table->unsignedTinyInteger('source');
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
        Schema::dropIfExists('orders');
    }
}
