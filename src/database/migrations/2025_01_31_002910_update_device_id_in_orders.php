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
        DB::table('orders')
            ->where('id', '<', 36951)
            ->whereNotNull('device_id')
            ->update([
                'device_id' => DB::raw('(SELECT id FROM devices WHERE devices.web_id = orders.device_id LIMIT 1)'),
            ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('device_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('device_id', 32)->nullable()->change();
        });
    }
};
