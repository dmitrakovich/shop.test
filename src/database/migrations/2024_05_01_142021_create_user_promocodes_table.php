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
        Schema::create('user_promocodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('promocode_id');
            $table->unsignedSmallInteger('apply_count')->default(0);
            $table->timestamp('applied_at');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['user_id', 'promocode_id']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('cancel_promocode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_promocodes');

        Schema::table('carts', function (Blueprint $table) {
            $table->boolean('cancel_promocode')->default(false);
        });
    }
};
