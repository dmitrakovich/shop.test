<?php

use App\Models\Orders\OrderItemStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
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
            $table->string('status_key', 20)->index()
                ->default(OrderItemStatus::getDefaultValue());
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
        Schema::dropIfExists('order_items');
    }
}
