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
            $table->timestamp('offline_notifications_pause_until')->nullable()->after('group_chat_id');
        });

        Schema::table('telegram_chats', function (Blueprint $table) {
            $table->dropColumn('offline_notifications_pause_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_chats', function (Blueprint $table) {
            $table->timestamp('offline_notifications_pause_until')->nullable()->after('telegram_bot_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('offline_notifications_pause_until');
        });
    }
};
