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
            $table->unsignedInteger('label_id')->nullable()->default(null)->change();
        });

        DB::table('products')->where('label_id', 0)->update(['label_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')->whereNull('label_id')->update(['label_id' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('label_id')->nullable(false)->default(0)->change();
        });
    }
};
