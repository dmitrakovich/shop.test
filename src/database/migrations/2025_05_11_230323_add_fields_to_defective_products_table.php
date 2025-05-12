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
        Schema::table('defective_products', function (Blueprint $table) {
            $table->foreignId('stock_id')->after('size_id')->index()->comment('id склада');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('defective_products', function (Blueprint $table) {
            $table->dropColumn('stock_id');
            $table->dropSoftDeletes();
        });
    }
};
