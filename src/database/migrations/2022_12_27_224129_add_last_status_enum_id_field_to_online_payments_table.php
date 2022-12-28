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
        Schema::table('online_payments', function (Blueprint $table) {
            $table->tinyInteger('last_status_enum_id')->default(1)->unsigned()->nullable()->comment('enum ID последнего статуса платежа');
            $table->double('paid_amount', 12, 2)->nullable()->comment('Оплаченная клиентом сумма');
            $table->integer('admin_user_id')->unsigned()->nullable()->change();
        });
        Schema::table('online_payment_statuses', function (Blueprint $table) {
          $table->integer('admin_user_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('online_payments', function (Blueprint $table) {
            $table->dropColumn('last_status_enum_id');
            $table->dropColumn('paid_amount');
            $table->integer('admin_user_id')->unsigned()->change();
        });
        Schema::table('online_payment_statuses', function (Blueprint $table) {
          $table->integer('admin_user_id')->unsigned()->change();
        });
    }
};
