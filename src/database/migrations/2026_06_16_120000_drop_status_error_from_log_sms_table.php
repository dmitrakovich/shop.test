<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('log_sms')
            ->whereNotNull('status_error')
            ->whereNull('status')
            ->update(['status' => DB::raw('status_error')]);

        Schema::table('log_sms', function (Blueprint $table) {
            $table->dropColumn('status_error');
        });
    }

    public function down(): void
    {
        Schema::table('log_sms', function (Blueprint $table) {
            $table->string('status_error')->nullable()->after('read_at');
        });
    }
};
