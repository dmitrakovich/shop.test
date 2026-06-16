<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_sms', function (Blueprint $table) {
            $table->string('delivery_channel', 16)->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('delivery_channel');
            $table->timestamp('read_at')->nullable()->after('delivered_at');
            $table->string('status_error')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('log_sms', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_channel',
                'delivered_at',
                'read_at',
                'status_error',
            ]);
        });
    }
};
