<?php

use App\Enums\Config\ConfigKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configs')->updateOrInsert(
            ['key' => ConfigKey::Feedback],
            [
                'config' => json_encode([
                    'discount' => [
                        'photo' => [
                            'BYN' => '10',
                            'USD' => '5',
                            'KZT' => '1500',
                            'RUB' => '350',
                        ],
                        'video' => [
                            'BYN' => '20',
                            'USD' => '10',
                            'KZT' => '3000',
                            'RUB' => '700',
                        ],
                    ],
                    'send_after' => '72',
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('configs')->updateOrInsert(
            ['key' => ConfigKey::Feedback],
            [
                'config' => json_encode([
                    'discount' => [
                        'BYN' => '10',
                        'USD' => '5',
                        'KZT' => '1500',
                        'RUB' => '350',
                    ],
                    'send_after' => '72',
                ], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ],
        );
    }
};
