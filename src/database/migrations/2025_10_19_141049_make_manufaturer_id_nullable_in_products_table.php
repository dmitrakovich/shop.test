<?php

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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('manufacturer_id')->nullable()->change();
        });

        DB::table('products')->where('manufacturer_id', 0)->update(['manufacturer_id' => null]);

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });

        DB::table('products')->whereNull('manufacturer_id')->update(['manufacturer_id' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('manufacturer_id')->nullable(false)->default(0)->change();
        });
    }
};
