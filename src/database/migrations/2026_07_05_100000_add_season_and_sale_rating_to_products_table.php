<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->integer('season_rating')->default(0)->after('newness_rating')->index();
            $table->integer('sale_rating')->default(0)->after('season_rating')->index();
        });

        $this->extendRatingConfig();
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['season_rating', 'sale_rating']);
        });

        $this->stripSeasonAndSaleAlgorithmIdsFromConfig();
    }

    private function extendRatingConfig(): void
    {
        $config = $this->ratingConfig();
        $popularityAlgorithmId = (int)($config['popularity_algorithm_id'] ?? 0);
        $saleAlgorithmId = (int)(DB::table('rating_algorithms')->where('name', 'Распродажа')->value('id') ?? 0);

        $config['season_algorithm_id'] = $config['season_algorithm_id'] ?? $popularityAlgorithmId;
        $config['sale_algorithm_id'] = $config['sale_algorithm_id'] ?? ($saleAlgorithmId ?: $popularityAlgorithmId);

        DB::table('configs')
            ->where('key', 'rating')
            ->update([
                'config' => json_encode($config, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    private function stripSeasonAndSaleAlgorithmIdsFromConfig(): void
    {
        $config = $this->ratingConfig();
        unset($config['season_algorithm_id'], $config['sale_algorithm_id']);

        DB::table('configs')
            ->where('key', 'rating')
            ->update([
                'config' => json_encode($config, JSON_THROW_ON_ERROR),
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
};
