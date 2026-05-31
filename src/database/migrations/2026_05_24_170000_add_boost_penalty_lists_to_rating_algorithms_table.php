<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rating_algorithms', function (Blueprint $table): void {
            $table->json('category_up_ids')->nullable()->after('product_down_coefficient');
            $table->json('category_down_ids')->nullable()->after('category_up_ids');
            $table->json('product_up_ids')->nullable()->after('category_down_ids');
            $table->json('product_down_ids')->nullable()->after('product_up_ids');
        });

        $this->migrateGlobalListsToAlgorithms();
        $this->stripGlobalListsFromConfig();
    }

    public function down(): void
    {
        Schema::table('rating_algorithms', function (Blueprint $table): void {
            $table->dropColumn([
                'category_up_ids',
                'category_down_ids',
                'product_up_ids',
                'product_down_ids',
            ]);
        });
    }

    private function migrateGlobalListsToAlgorithms(): void
    {
        $config = $this->ratingConfig();
        $lists = [
            'category_up_ids' => $this->parseIds($config['category_up_ids'] ?? []),
            'category_down_ids' => $this->parseIds($config['category_down_ids'] ?? []),
            'product_up_ids' => $this->parseIds($config['product_up_ids'] ?? []),
            'product_down_ids' => $this->parseIds($config['product_down_ids'] ?? []),
        ];

        if (
            $lists['category_up_ids'] === []
            && $lists['category_down_ids'] === []
            && $lists['product_up_ids'] === []
            && $lists['product_down_ids'] === []
        ) {
            return;
        }

        $algorithmIds = array_values(array_unique(array_filter([
            (int)($config['popularity_algorithm_id'] ?? 0),
            (int)($config['newness_algorithm_id'] ?? 0),
        ])));

        foreach ($algorithmIds as $algorithmId) {
            DB::table('rating_algorithms')
                ->where('id', $algorithmId)
                ->update([
                    'category_up_ids' => json_encode($lists['category_up_ids'], JSON_THROW_ON_ERROR),
                    'category_down_ids' => json_encode($lists['category_down_ids'], JSON_THROW_ON_ERROR),
                    'product_up_ids' => json_encode($lists['product_up_ids'], JSON_THROW_ON_ERROR),
                    'product_down_ids' => json_encode($lists['product_down_ids'], JSON_THROW_ON_ERROR),
                ]);
        }
    }

    private function stripGlobalListsFromConfig(): void
    {
        $config = $this->ratingConfig();

        DB::table('configs')
            ->where('key', 'rating')
            ->update([
                'config' => json_encode([
                    'popularity_algorithm_id' => $config['popularity_algorithm_id'] ?? null,
                    'newness_algorithm_id' => $config['newness_algorithm_id'] ?? null,
                    'last_update' => $config['last_update'] ?? null,
                ], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function ratingConfig(): array
    {
        $config = DB::table('configs')->where('key', 'rating')->value('config');

        return is_string($config) ? json_decode($config, true, 512, JSON_THROW_ON_ERROR) : [];
    }

    /**
     * @return list<int>
     */
    private function parseIds(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, 'is_numeric'))));
    }
};
