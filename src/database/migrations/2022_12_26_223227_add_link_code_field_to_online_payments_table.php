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
            $table->string('link_code', 64)->comment('Уникальный код ссылки на платеж')->nullable();
            $table->dateTime('link_expires_at')->comment('Времся жизни ссылки на платеж')->nullable();
            $table->json('request_data')->nullable();
            $table->index('link_code');
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
            $table->dropIndex(['link_code']);
            $table->dropColumn('link_code');
            $table->dropColumn('link_expires_at');
            $table->dropColumn('request_data');
        });
    }
};
