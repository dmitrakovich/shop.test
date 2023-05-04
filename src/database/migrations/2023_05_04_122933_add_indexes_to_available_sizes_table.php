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
        Schema::table('available_sizes', function (Blueprint $table) {
            $table->index('sku');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_sizes', function (Blueprint $table) {
            $table->dropIndex('available_sizes_sku_index');
            $table->dropIndex('available_sizes_category_id_index');
            $table->dropIndex('available_sizes_brand_id_index');
            $table->dropIndex('available_sizes_stock_id_index');
        });
    }
};
