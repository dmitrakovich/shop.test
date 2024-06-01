<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offline_orders', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->index(['receipt_number']);
            $table->foreignId('one_c_product_id')->index()->after('product_id')->comment('External product id from 1C');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_orders', function (Blueprint $table) {
            $table->dropIndex(['receipt_number']);
            $table->unique(['receipt_number']);
            $table->dropColumn('one_c_product_id');
        });
    }
};
