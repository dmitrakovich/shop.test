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
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned()->comment('Номер заказа');
            $table->string('currency_code', 8)->nullable()->comment('Код валюты в ISO 4217');
            $table->double('currency_value', 10, 2)->default(1)->comment('Валютный курс');
            $table->tinyInteger('method_enum_id')->unsigned()->nullable()->comment('enum ID платежной системы');
            $table->integer('admin_user_id')->unsigned()->comment('ID admin пользователя');
            $table->double('amount', 12, 2)->nullable()->comment('Сумма оплаты');
            $table->datetime('expires_at')->nullable()->comment('Время жизни платежа');
            $table->string('payment_id', 128)->nullable()->comment('ID платежа в платежной системе');
            $table->string('payment_url', 256)->nullable()->comment('Ссылка на платеж');
            $table->tinyInteger('card_last4')->unsigned()->nullable()->comment('Последние 4 цифры карты');
            $table->string('card_type', 64)->nullable()->comment('Тип карты');
            $table->string('email', 128)->nullable()->comment('Email плательщика');
            $table->string('phone', 32)->nullable()->comment('Телефон плательщика');
            $table->string('fio', 255)->nullable()->comment('ФИО плательщика');
            $table->text('comment')->nullable()->comment('Комментарий');
            $table->boolean('is_test')->nullable()->comment('Тестовый платеж');
            $table->datetime('payed_at')->nullable()->comment('Дата оплаты платежа');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('admin_user_id')->references('id')->on('admin_users');

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
        Schema::dropIfExists('online_payments');
    }
};
