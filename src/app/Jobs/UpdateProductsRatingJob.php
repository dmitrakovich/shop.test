<?php

namespace App\Jobs;

use App\Enums\Product\RatingFactor;
use App\Models\Config;
use App\Models\RatingAlgorithm;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use stdClass;

class UpdateProductsRatingJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const string CONFIG_KEY = 'rating';

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->log('Старт');

        $configModel = Config::query()->findOrFail(self::CONFIG_KEY);
        $config = $this->normalizeConfig($configModel->config);
        $algorithms = $this->getAlgorithms($config);
        $products = $this->getProductsData();

        if ($products->isEmpty()) {
            $this->saveConfig($configModel, $config);
            $this->log('0 товаров');
            $this->log('Успешно выполнено');

            return;
        }

        $productIds = $products->pluck('id')->map(fn ($id): int => (int)$id)->all();
        $metrics = $this->getMetrics($productIds);
        $ranges = $this->getRanges($products, $metrics);
        $rating = [];

        foreach ($products as $product) {
            $scores = $this->scoresForProduct($product, $metrics, $ranges, $config);

            $rating[(int)$product->id] = [
                'rating' => $this->calculateRating($scores, $algorithms['popularity']),
                'newness_rating' => $this->calculateRating($scores, $algorithms['newness']),
            ];
        }

        $this->updateProductsRating($rating);
        $this->saveConfig($configModel, $config);

        $this->log(count($rating) . ' товаров');
        $this->log('Успешно выполнено');
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{popularity: RatingAlgorithm, newness: RatingAlgorithm}
     */
    private function getAlgorithms(array $config): array
    {
        $popularityAlgorithmId = (int)$config['popularity_algorithm_id'];
        $newnessAlgorithmId = (int)$config['newness_algorithm_id'];

        $algorithms = RatingAlgorithm::query()
            ->whereIn('id', [$popularityAlgorithmId, $newnessAlgorithmId])
            ->get()
            ->keyBy('id');

        $popularityAlgorithm = $algorithms->get($popularityAlgorithmId);
        $newnessAlgorithm = $algorithms->get($newnessAlgorithmId);

        if (!$popularityAlgorithm instanceof RatingAlgorithm || !$newnessAlgorithm instanceof RatingAlgorithm) {
            throw new \Exception('Не выбран алгоритм рейтинга для популярности или новинок');
        }

        return [
            'popularity' => $popularityAlgorithm,
            'newness' => $newnessAlgorithm,
        ];
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function getProductsData(): Collection
    {
        return DB::table('products')
            ->leftJoin('seasons', 'products.season_id', '=', 'seasons.id')
            ->whereNull('products.deleted_at')
            ->where('products.price', '<>', 0)
            ->selectRaw(<<<'SQL'
                products.id,
                products.price,
                products.old_price,
                products.category_id,
                DATEDIFF(CURDATE(), DATE(products.created_at)) AS days_from_created_at,
                IF(seasons.is_actual = 1, 1, 0) AS season_is_actual
            SQL)
            ->get();
    }

    /**
     * @param  list<int>  $productIds
     * @return array<string, array<int, float>>
     *
     * @throws \Exception
     */
    private function getMetrics(array $productIds): array
    {
        $views = array_fill_keys($productIds, 0.0);
        $purchases = array_fill_keys($productIds, 0.0);
        $carts = array_fill_keys($productIds, 0.0);
        $counterYandexId = config('services.yandex.counter_id');

        $popularResult = $this->getYandexMetrikaData([
            'ids' => $counterYandexId,
            'metrics' => 'ym:s:productImpressionsUniq,ym:s:productPurchasedUniq',
            'dimensions' => 'ym:s:productID',
            'date1' => '30daysAgo',
            'date2' => 'yesterday',
            'sort' => 'ym:s:productID',
            'limit' => 3000,
        ]);

        foreach ($popularResult['data'] as $row) {
            $productId = (int)($row['dimensions'][0]['name'] ?? 0);
            if (array_key_exists($productId, $views)) {
                $views[$productId] = (float)($row['metrics'][0] ?? 0);
                $purchases[$productId] = (float)($row['metrics'][1] ?? 0);
            }
        }

        $cartsResult = $this->getYandexMetrikaData([
            'ids' => $counterYandexId,
            'metrics' => 'ym:s:productBasketsUniq',
            'dimensions' => 'ym:s:productID',
            'date1' => '7daysAgo',
            'date2' => 'yesterday',
            'sort' => 'ym:s:productID',
            'limit' => 3000,
        ]);

        foreach ($cartsResult['data'] as $row) {
            $productId = (int)($row['dimensions'][0]['name'] ?? 0);
            if (array_key_exists($productId, $carts)) {
                $carts[$productId] = (float)($row['metrics'][0] ?? 0);
            }
        }

        return [
            RatingFactor::Views->value => $views,
            RatingFactor::Purchases->value => $purchases,
            RatingFactor::Carts->value => $carts,
        ];
    }

    /**
     * @param  Collection<int, stdClass>  $products
     * @param  array<string, array<int, float>>  $metrics
     * @return array<string, array{min: float, max: float}>
     */
    private function getRanges(Collection $products, array $metrics): array
    {
        $prices = [];
        $discounts = [];

        foreach ($products as $product) {
            $prices[] = (float)$product->price;
            $discounts[] = $this->discountPercent((float)$product->price, (float)$product->old_price);
        }

        return [
            RatingFactor::Views->value => $this->range($metrics[RatingFactor::Views->value]),
            RatingFactor::Carts->value => $this->range($metrics[RatingFactor::Carts->value]),
            RatingFactor::Purchases->value => $this->range($metrics[RatingFactor::Purchases->value]),
            RatingFactor::Price->value => $this->range($prices),
            RatingFactor::Discount->value => $this->range($discounts),
        ];
    }

    /**
     * @param  array<string, array<int, float>>  $metrics
     * @param  array<string, array{min: float, max: float}>  $ranges
     * @param  array<string, mixed>  $config
     * @return array<string, float>
     */
    private function scoresForProduct(stdClass $product, array $metrics, array $ranges, array $config): array
    {
        $productId = (int)$product->id;
        $categoryId = (int)$product->category_id;
        $days = max(0, (int)$product->days_from_created_at);

        return [
            RatingFactor::Views->value => $this->minMaxScore($metrics[RatingFactor::Views->value][$productId] ?? 0.0, $ranges[RatingFactor::Views->value]),
            RatingFactor::Carts->value => $this->minMaxScore($metrics[RatingFactor::Carts->value][$productId] ?? 0.0, $ranges[RatingFactor::Carts->value]),
            RatingFactor::Purchases->value => $this->minMaxScore($metrics[RatingFactor::Purchases->value][$productId] ?? 0.0, $ranges[RatingFactor::Purchases->value]),
            RatingFactor::Price->value => $this->minMaxScore((float)$product->price, $ranges[RatingFactor::Price->value]),
            RatingFactor::Discount->value => $this->minMaxScore($this->discountPercent((float)$product->price, (float)$product->old_price), $ranges[RatingFactor::Discount->value]),
            RatingFactor::CategoryUp->value => in_array($categoryId, $config['category_up_ids'], true) ? 100.0 : 0.0,
            RatingFactor::CategoryDown->value => in_array($categoryId, $config['category_down_ids'], true) ? -100.0 : 0.0,
            RatingFactor::Season->value => (bool)$product->season_is_actual ? 100.0 : 0.0,
            RatingFactor::CreatedAt->value => 100 / sqrt($days + 1),
            RatingFactor::ProductUp->value => in_array($productId, $config['product_up_ids'], true) ? 100.0 : 0.0,
            RatingFactor::ProductDown->value => in_array($productId, $config['product_down_ids'], true) ? -100.0 : 0.0,
        ];
    }

    /**
     * @param  array<string, float>  $scores
     */
    private function calculateRating(array $scores, RatingAlgorithm $algorithm): int
    {
        $rating = 0.0;

        foreach (RatingFactor::cases() as $factor) {
            $rating += ($scores[$factor->value] ?? 0.0) * $algorithm->coefficientFor($factor);
        }

        return (int)round($rating);
    }

    /**
     * @param  array<int, array{rating: int, newness_rating: int}>  $rating
     */
    private function updateProductsRating(array $rating): void
    {
        foreach (array_chunk($rating, 1000, true) as $ratingChunk) {
            $ratingCases = '';
            $newnessRatingCases = '';
            $productIds = [];

            foreach ($ratingChunk as $id => $values) {
                $productIds[] = (int)$id;
                $ratingCases .= "WHEN id = {$id} THEN {$values['rating']} ";
                $newnessRatingCases .= "WHEN id = {$id} THEN {$values['newness_rating']} ";
            }

            $ids = implode(',', $productIds);

            DB::statement(
                "UPDATE products SET rating = (CASE {$ratingCases} ELSE rating END), " .
                "newness_rating = (CASE {$newnessRatingCases} ELSE newness_rating END) WHERE id IN ({$ids})"
            );
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function normalizeConfig(array $config): array
    {
        return [
            'popularity_algorithm_id' => (int)($config['popularity_algorithm_id'] ?? 0),
            'newness_algorithm_id' => (int)($config['newness_algorithm_id'] ?? 0),
            'category_up_ids' => $this->ids($config['category_up_ids'] ?? []),
            'category_down_ids' => $this->ids($config['category_down_ids'] ?? []),
            'product_up_ids' => $this->ids($config['product_up_ids'] ?? []),
            'product_down_ids' => $this->ids($config['product_down_ids'] ?? []),
            'last_update' => $config['last_update'] ?? null,
        ];
    }

    /**
     * @return list<int>
     */
    private function ids(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, 'is_numeric'))));
    }

    /**
     * @param  array{min: float, max: float}  $range
     */
    private function minMaxScore(float $value, array $range): float
    {
        if ($range['max'] === $range['min']) {
            return 0.0;
        }

        $raw = ($value - $range['min']) / ($range['max'] - $range['min']) * 100;

        return min(100.0, max(0.0, ceil($raw / 2) * 2));
    }

    private function discountPercent(float $price, float $oldPrice): float
    {
        if ($oldPrice <= 0 || $oldPrice <= $price) {
            return 0.0;
        }

        return (1 - ($price / $oldPrice)) * 100;
    }

    /**
     * @param  array<int|string, float|int>  $values
     * @return array{min: float, max: float}
     */
    private function range(array $values): array
    {
        if ($values === []) {
            return ['min' => 0.0, 'max' => 0.0];
        }

        return [
            'min' => (float)min($values),
            'max' => (float)max($values),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function saveConfig(Config $configModel, array $config): void
    {
        $config['last_update'] = now()->toDateTimeString();
        $configModel->update(['config' => $config]);
    }

    /**
     * Get metrika data from yandex api.
     *
     * @throws \Exception
     */
    private function getYandexMetrikaData(array $params): array
    {
        $result = Http::withToken(config('services.yandex.token'), 'OAuth')
            ->get('https://api-metrika.yandex.ru/stat/v1/data', $params)
            ->json();

        if (empty($result) || isset($result['errors'])) {
            throw new \Exception($result['message'] ?? 'Яндекс метрика не вернула данные');
        }

        return $result;
    }
}
