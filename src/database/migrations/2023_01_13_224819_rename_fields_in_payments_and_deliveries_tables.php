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
        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->renameColumn('class', 'instance');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->renameColumn('class', 'instance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->renameColumn('instance', 'class');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->renameColumn('instance', 'class');
        });
    }
};
