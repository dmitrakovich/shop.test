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
            $table->string('sku', 30)->after('count')->comment('product stock keeping unit');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->char('code', 3)->comment('currency code 3 symbol (ISO 4217)')->change();
            $table->char('country', 2)->comment('country code 2 symbol (ISO 3166-1)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_orders', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
