<?php

use App\Models\Device;
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
            $table->string('google_id', 32)->index()->nullable();
            $table->enum('type', Device::TYPES);
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
