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
        Schema::table('log_order_item_inventory_notifications', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropUnique('log_order_item_inventory_notifications_order_item_id_unique');
            $table->unique(['order_item_id', 'deleted_at'], 'order_item_id_deleted_at_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_order_item_inventory_notifications', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropUnique('order_item_id_deleted_at_unique');
            $table->unique('order_item_id');
        });
    }
};
