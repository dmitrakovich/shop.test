<?php

use Deliveries\ShopPvz;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('delivery_methods')->insert([
            'id' => ShopPvz::ID,
            'name' => 'Собственный ПВЗ',
            'instance' => 'ShopPvz',
            'active' => true,
            'created_at' => now(),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('stock_id')->nullable()->index()->after('delivery_id')->comment('Warehouse (Stock) from which the order will be picked up');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('delivery_methods')->where('id', ShopPvz::ID)->delete();

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_id');
        });
    }
};
