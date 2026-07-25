<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Config::query()->updateOrCreate(
            ['key' => 'feedback'],
            [
                'config' => [
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
                ],
            ],
        );
    }

    public function down(): void
    {
        Config::query()->updateOrCreate(
            ['key' => 'feedback'],
            [
                'config' => [
                    'discount' => [
                        'BYN' => '10',
                        'USD' => '5',
                        'KZT' => '1500',
                        'RUB' => '350',
                    ],
                    'send_after' => '72',
                ],
            ],
        );
    }
};
