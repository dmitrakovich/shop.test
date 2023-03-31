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
        Schema::table('stocks', function (Blueprint $table) {
            $table->string('one_c_id', 20)->unique()->change();
            $table->boolean('check_availability')->default(false)->after('geo_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['one_c_id']);
            $table->foreignId('one_c_id')->change();
            $table->dropColumn('check_availability');
        });
    }
};
