<?php

use App\Models\Enum\OrderMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

            $table->unsignedFloat('total_price')->default(0);
            $table->string('currency', 5);
            $table->float('rate');

            $table->foreignId('country_id')->nullable();
            $table->string('region', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('user_addr')->nullable();

            $table->foreignId('payment_id')->nullable();
            $table->float('payment_cost')->nullable();
            $table->foreignId('delivery_id')->nullable();
            $table->float('delivery_cost')->nullable();
            $table->foreignId('delivery_point_id')->nullable();

            $table->enum('order_method', OrderMethod::getValues())
                ->default(OrderMethod::DEFAULT);

            $table->string('utm_medium', 40)->nullable();
            $table->string('utm_source', 40)->nullable();
            $table->string('utm_campaign', 40)->nullable();
            $table->string('utm_content', 40)->nullable();
            $table->string('utm_term', 40)->nullable();

            $table->foreignId('status_id')->default(0);

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
