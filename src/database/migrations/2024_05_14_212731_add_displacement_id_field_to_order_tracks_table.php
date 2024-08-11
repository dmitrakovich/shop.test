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
        Schema::table('order_tracks', function (Blueprint $table) {
            $table->foreignId('displacement_id')->nullable()->comment('ID перемещения')->constrained('displacements')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tracks', function (Blueprint $table) {
            $table->dropForeign(['displacement_id']);
            $table->dropColumn('displacement_id');
        });
    }
};
