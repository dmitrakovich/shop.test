<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configs', function (Blueprint $table): void {
            $table->dropPrimary();
        });

        Schema::table('configs', function (Blueprint $table): void {
            $table->id()->first();
            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table): void {
            $table->dropUnique(['key']);
        });

        Schema::table('configs', function (Blueprint $table): void {
            $table->dropColumn('id');
        });

        // MySQL requires a primary key; restore the legacy natural key.
        DB::statement('ALTER TABLE `configs` ADD PRIMARY KEY (`key`)');
    }
};
