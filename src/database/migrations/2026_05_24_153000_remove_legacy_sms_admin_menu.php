<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('admin_menu')) {
            return;
        }

        DB::table('admin_menu')
            ->whereIn('uri', [
                'config/sms',
                'send-sms',
                'logs/sms',
            ])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * The removed legacy SMS admin entries are intentionally not restored.
     */
    public function down(): void {}
};
