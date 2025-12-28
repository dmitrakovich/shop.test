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
        Schema::dropIfExists('order_item_statuses');
        Schema::dropIfExists('order_statuses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->string('key', 20)->primary();
            $table->string('name_for_admin', 100);
            $table->string('name_for_user', 100);
            $table->integer('sorting')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_item_statuses', function (Blueprint $table) {
            $table->string('key', 20)->primary();
            $table->string('name_for_admin', 100);
            $table->string('name_for_user', 100);
            $table->integer('sorting')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
