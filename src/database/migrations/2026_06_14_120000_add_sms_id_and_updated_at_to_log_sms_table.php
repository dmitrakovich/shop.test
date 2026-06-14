<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_sms', function (Blueprint $table) {
            $table->string('sms_id', 64)->nullable()->after('text');
            $table->timestamp('updated_at')->nullable()->after('status');

            $table->index('sms_id');
        });
    }

    public function down(): void
    {
        Schema::table('log_sms', function (Blueprint $table) {
            $table->dropIndex(['sms_id']);
            $table->dropColumn(['sms_id', 'updated_at']);
        });
    }
};
