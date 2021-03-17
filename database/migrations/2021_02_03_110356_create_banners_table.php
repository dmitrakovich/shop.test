<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->enum('position', [
                'catalog_top',
                'index_top',
                'index_bottom',
            ])->index();

            // $table->enum('type', [
            //     'image',
            //     'video'
            // ])->default('image');
            // $table->string('resource')->nullable();

            $table->string('title')->nullable();
            $table->string('url')->nullable();

            $table->unsignedInteger('priority')->default(0);

            $table->boolean('active')->default(true);
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners');
    }
}
