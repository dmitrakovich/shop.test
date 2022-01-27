<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->foreignId('user_id')->index()->nullable();
            $table->foreignId('cart_id')->index()->nullable();
            $table->foreignId('yandex_id')->index()->nullable();
            $table->foreignId('google_id')->index()->nullable();
            $table->enum('type', ['mobile', 'desktop']);
            $table->string('agent');
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
        Schema::dropIfExists('devices');
    }
}
