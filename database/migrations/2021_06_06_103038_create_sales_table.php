<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('label_text')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->enum('algorithm', ['fake', 'simple', 'count', 'ascending'])->default('simple');
            $table->string('sale');
            $table->text('categories')->nullable();
            $table->text('collections')->nullable();
            $table->text('styles')->nullable();
            $table->text('seasons')->nullable();
            $table->boolean('only_new')->default(false);
            $table->boolean('add_client_sale')->default(false);
            $table->boolean('has_installment')->default(true);
            $table->boolean('has_fitting')->default(true);
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
        Schema::dropIfExists('sales');
    }
}
