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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('country_id')->unsigned()->comment('ID страны');
            $table->string('name', 128)->comment('Название города');
            $table->string('slug', 128)->unique()->comment('Slug города');
            $table->string('catalog_title', 128)->nullable()->comment('Загаловок в каталоге');

            $table->foreign('country_id')->references('id')->on('countries');
            $table->index('slug');
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
        Schema::dropIfExists('cities');
    }
};
