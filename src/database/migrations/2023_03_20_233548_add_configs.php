<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('configs')->insertOrIgnore([
            [
                'key' => 'installment',
                'config' => '{"min_price": "100.00"}',
                'created_at' => $now,
            ],
            [
                'key' => 'feedback',
                'config' => '{"discount": {"BYN": "10", "KZT": "1500", "RUB": "350", "USD": "5"}, "send_after": "72"}',
                'created_at' => $now,
            ],
            [
                'key' => 'sms',
                'config' => '{"enabled": "on"}',
                'created_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
