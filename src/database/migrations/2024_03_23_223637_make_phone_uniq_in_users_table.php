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
        Schema::table('users', function (Blueprint $table) {
            $table->index(['phone']);
        });

        $userDuplicateIds = DB::table('users as u1')
            ->join('users as u2', function ($join) {
                $join->on('u1.phone', '=', 'u2.phone')->whereColumn('u1.id', '>', 'u2.id');
            })
            ->select('u1.id')
            ->pluck('id')
            ->toArray();

        DB::table('user_addresses')->whereIn('user_id', $userDuplicateIds)->delete();
        DB::table('users')->whereIn('id', $userDuplicateIds)->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['phone']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->string('phone', 20)->nullable(true)->change();
        });
    }
};
