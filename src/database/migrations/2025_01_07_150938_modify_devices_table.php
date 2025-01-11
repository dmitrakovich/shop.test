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
        Schema::table('devices', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->renameColumn('id', 'web_id');
            $table->id()->first();

            $table->string('web_id', 32)->nullable()->change();
            $table->string('api_id', 36)->unique()->nullable()->after('web_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->renameColumn('web_id', 'id');

            $table->string('web_id', 32)->nullable(false)->primary()->change();
            $table->dropColumn('api_id');
        });
    }
};
