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
            $table->renameColumn('chat_id', 'group_chat_id');
            $table->foreignId('private_chat_id')->index()->nullable()->after('city_id');
        });

        Schema::table('log_order_item_inventory_notifications', function (Blueprint $table) {
            $table->timestamp('picked_up_at')->nullable()->after('confirmed_at');
            $table->timestamp('collected_at')->nullable()->after('confirmed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->renameColumn('group_chat_id', 'chat_id');
            $table->dropColumn('private_chat_id');
        });

        Schema::table('log_order_item_inventory_notifications', function (Blueprint $table) {
            $table->dropColumn('collected_at');
            $table->dropColumn('picked_up_at');
        });
    }
};
