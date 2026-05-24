<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_algorithms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('views_coefficient')->default(0);
            $table->integer('carts_coefficient')->default(0);
            $table->integer('purchases_coefficient')->default(0);
            $table->integer('price_coefficient')->default(0);
            $table->integer('discount_coefficient')->default(0);
            $table->integer('category_up_coefficient')->default(0);
            $table->integer('category_down_coefficient')->default(0);
            $table->integer('season_coefficient')->default(0);
            $table->integer('created_at_coefficient')->default(0);
            $table->integer('product_up_coefficient')->default(0);
            $table->integer('product_down_coefficient')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->integer('newness_rating')->default(0)->after('rating')->index();
        });

        $config = $this->ratingConfig();
        $algorithmIds = $this->seedAlgorithms($config);

        DB::table('configs')->updateOrInsert(
            ['key' => 'rating'],
            [
                'config' => json_encode($this->normalizeConfig($config, $algorithmIds), JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('newness_rating');
        });

        Schema::dropIfExists('rating_algorithms');
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
     * @param  array<string, mixed>  $config
     * @return array<string, int>
     */
    private function seedAlgorithms(array $config): array
    {
        $legacyAlgorithms = Arr::get($config, 'algoritm', []);
        $legacyNames = Arr::get($config, 'algoritm_name', []);
        $algorithmIds = [];

        foreach ($legacyAlgorithms as $key => $legacyAlgorithm) {
            if (!is_array($legacyAlgorithm)) {
                continue;
            }

            $id = DB::table('rating_algorithms')->insertGetId([
                'name' => (string)($legacyNames[$key] ?? $key),
                'views_coefficient' => (int)($legacyAlgorithm['popular'] ?? 0),
                'carts_coefficient' => (int)($legacyAlgorithm['trand'] ?? 0),
                'purchases_coefficient' => (int)($legacyAlgorithm['purshase'] ?? 0),
                'price_coefficient' => (int)($legacyAlgorithm['price'] ?? 0),
                'discount_coefficient' => (int)($legacyAlgorithm['discount'] ?? 0),
                'category_up_coefficient' => 0,
                'category_down_coefficient' => (int)($legacyAlgorithm['category'] ?? 0),
                'season_coefficient' => (int)($legacyAlgorithm['season'] ?? 0),
                'created_at_coefficient' => (int)($legacyAlgorithm['newless'] ?? 0),
                'product_up_coefficient' => 0,
                'product_down_coefficient' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $algorithmIds[(string)$key] = (int)$id;
        }

        if ($algorithmIds === []) {
            $algorithmIds['popularity'] = DB::table('rating_algorithms')->insertGetId([
                'name' => 'Популярность',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $algorithmIds['newness'] = DB::table('rating_algorithms')->insertGetId([
                'name' => 'Новинки',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $algorithmIds;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, int>  $algorithmIds
     * @return array<string, mixed>
     */
    private function normalizeConfig(array $config, array $algorithmIds): array
    {
        $currentAlgorithm = (string)Arr::get($config, 'curr_algoritm', '');

        return [
            'popularity_algorithm_id' => $algorithmIds[$currentAlgorithm] ?? reset($algorithmIds),
            'newness_algorithm_id' => $algorithmIds['new'] ?? reset($algorithmIds),
            'category_up_ids' => [],
            'category_down_ids' => $this->parseIds((string)Arr::get($config, 'false_category', '')),
            'product_up_ids' => [],
            'product_down_ids' => [],
            'last_update' => Arr::get($config, 'last_update'),
        ];
    }

    /**
     * @return list<int>
     */
    private function parseIds(string $value): array
    {
        return array_values(array_map('intval', array_filter(explode(',', $value), 'is_numeric')));
    }
};
