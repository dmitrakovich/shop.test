<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::table('installments')->truncate();

        Schema::table('installments', function (Blueprint $table) {
            $table->dropUnique(['contract_number']);
            $table->index('contract_number');
            $table->dropColumn('order_id');
            $table->foreignId('order_item_id')
                ->unique()
                ->after('id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropIndex(['contract_number']);
            $table->unique('contract_number');
            $table->foreignId('order_id');
            $table->dropForeign(['order_item_id']);
            $table->dropColumn('order_item_id');
        });
    }
};
