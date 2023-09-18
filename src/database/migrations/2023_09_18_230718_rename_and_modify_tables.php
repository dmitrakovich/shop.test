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
        Schema::rename('log_order_item_inventory_notifications', 'log_order_item_statuses');

        Schema::table('log_order_item_statuses', function (Blueprint $table) {
            $table->timestamp('moved_at')->nullable()->after('picked_up_at');
        });

        DB::statement('
            UPDATE log_order_item_statuses AS s
            JOIN log_order_item_pickup_statuses AS p ON s.order_item_id = p.order_item_id
            SET s.picked_up_at = p.created_at
        ');

        DB::statement('
            UPDATE log_order_item_statuses AS s
            JOIN log_order_item_pickup_statuses AS p ON s.order_item_id = p.order_item_id
            SET s.moved_at = p.updated_at
            WHERE p.moved = 1
        ');

        Schema::dropIfExists('log_order_item_pickup_statuses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('log_order_item_pickup_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique();
            $table->boolean('moved')->default(false);
            $table->timestamps();
        });

        $insertData = DB::table('log_order_item_statuses')
            ->whereNotNull('picked_up_at')
            ->whereNull('deleted_at')
            ->get(['order_item_id', 'moved_at', 'picked_up_at'])
            ->map(function ($orderItemStatus) {
                return [
                    'order_item_id' => $orderItemStatus->order_item_id,
                    'moved' => !empty($orderItemStatus->moved_at),
                    'created_at' => $orderItemStatus->picked_up_at,
                    'updated_at' => $orderItemStatus->moved_at,
                ];
            });
        DB::table('log_order_item_pickup_statuses')->insert($insertData->toArray());

        Schema::table('log_order_item_statuses', function (Blueprint $table) {
            $table->dropColumn('moved_at');
        });

        Schema::rename('log_order_item_statuses', 'log_order_item_inventory_notifications');
    }
};
