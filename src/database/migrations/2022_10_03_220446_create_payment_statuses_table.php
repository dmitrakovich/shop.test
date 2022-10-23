<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_payment_statuses', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('online_payment_id')->unsigned()->comment('ID платежа');
            $table->integer('admin_user_id')->unsigned()->comment('ID admin пользователя');
            $table->tinyInteger('payment_status_enum_id')->unsigned()->nullable()->comment('enum ID статуса плажета');

            $table->foreign('admin_user_id')->references('id')->on('admin_users');
            $table->foreign('online_payment_id')->references('id')->on('online_payments')->onDelete('cascade');

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
        Schema::dropIfExists('online_payment_statuses');
    }
};
