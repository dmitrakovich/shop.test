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
        Schema::create('log_order_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete()->nullable();
            $table->unsignedInteger('admin_id')->nullable();
            $table->text('action');
            $table->timestamp('created_at');

            $table->foreign('admin_id')->references('id')->on('admin_users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_order_actions');
    }
};
