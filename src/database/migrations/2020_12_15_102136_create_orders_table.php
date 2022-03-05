<?php

use App\Models\Enum\OrderMethod;
use App\Models\Orders\OrderStatus;
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
            $table->foreignId('user_id')->nullable()->index();
            $table->string('device_id', 32)->index()->nullable();
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('patronymic_name', 50)->nullable();
            $table->foreignId('promocode_id')->nullable()->index();
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
            $table->float('delivery_price')->nullable();
            $table->foreignId('delivery_point_id')->nullable();

            $table->enum('order_method', OrderMethod::getValues())
                ->default(OrderMethod::DEFAULT);

            $table->string('utm_medium', 40)->nullable();
            $table->string('utm_source', 40)->nullable();
            $table->string('utm_campaign', 40)->nullable();
            $table->string('utm_content', 40)->nullable();
            $table->string('utm_term', 40)->nullable();

            $table->string('status_key', 20)->index()
                ->default(OrderStatus::getDefaultValue());
            $table->foreignId('admin_id')->index()->nullable();

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
