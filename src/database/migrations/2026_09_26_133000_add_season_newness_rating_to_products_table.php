<?php

use App\Enums\Config\ConfigKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->integer('season_newness_rating')->default(0)->after('newness_rating')->index();
        });

        DB::table('products')->update([
            'season_newness_rating' => DB::raw('newness_rating'),
        ]);

        $this->extendRatingConfig();
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('season_newness_rating');
        });

        $this->stripSeasonNewnessAlgorithmIdFromConfig();
    }

    private function extendRatingConfig(): void
    {
        $config = $this->ratingConfig();
        $config['season_newness_algorithm_id'] = $config['season_newness_algorithm_id']
            ?? $config['newness_algorithm_id']
            ?? null;

        DB::table('configs')
            ->where('key', ConfigKey::Rating)
            ->update([
                'config' => json_encode($config, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    private function stripSeasonNewnessAlgorithmIdFromConfig(): void
    {
        $config = $this->ratingConfig();
        unset($config['season_newness_algorithm_id']);

        DB::table('configs')
            ->where('key', ConfigKey::Rating)
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
        $config = DB::table('configs')->where('key', ConfigKey::Rating)->value('config');

        return is_string($config) ? json_decode($config, true, 512, JSON_THROW_ON_ERROR) : [];
    }
};
