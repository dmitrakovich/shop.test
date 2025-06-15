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
        DB::statement("UPDATE orders SET phone = REGEXP_REPLACE(phone, '[^0-9]', '')");

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('phone')->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->string('phone', 20)->change();
        });
    }
};
