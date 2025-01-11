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
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->renameColumn('device_id', 'web_device_id');
            $table->index(['web_device_id']);
            $table->foreignId('device_id')
                ->index()
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table('favorites')
            ->join('devices', 'favorites.web_device_id', '=', 'devices.web_id')
            ->update(['device_id' => DB::raw('devices.id')]);

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn('web_device_id');
        });

        DB::table('favorites')
            ->whereNull('user_id')
            ->whereNull('device_id')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropIndex(['device_id']);
            $table->renameColumn('device_id', 'int_device_id');
            $table->index(['int_device_id']);
            $table->string('device_id', 32)->index()->nullable();
        });

        DB::table('favorites')
            ->join('devices', 'favorites.int_device_id', '=', 'devices.id')
            ->update(['device_id' => DB::raw('devices.web_id')]);

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn('int_device_id');
        });
    }
};
