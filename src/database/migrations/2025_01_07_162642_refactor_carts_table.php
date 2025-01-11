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
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('device_id')
                ->unique()
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->unique()
                ->nullable()
                ->after('device_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table('carts')
            ->join('users', 'users.cart_token', '=', 'carts.id')
            ->whereNotNull('users.cart_token')
            ->update(['carts.user_id' => DB::raw('users.id')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cart_token');
        });

        DB::table('carts')
            ->join('devices', 'devices.cart_id', '=', 'carts.id')
            ->whereNotNull('devices.cart_id')
            ->update(['carts.device_id' => DB::raw('devices.id')]);

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('cart_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('device_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('cart_token')->nullable()->unique();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('cart_id')->index()->nullable();
        });
    }
};
