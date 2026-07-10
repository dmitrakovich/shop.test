<?php

namespace App\Services\Api\Yandex;

use Illuminate\Support\Facades\Http;

class MetrikaService
{
    private const string API_URL = 'https://api-metrika.yandex.ru/stat/v1/data';

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function fetch(array $params): array
    {
        $response = Http::withToken(config('services.yandex.token'), 'OAuth')
            ->get(self::API_URL, $params);

        $result = $response->json();

        if (!$response->successful() || empty($result) || isset($result['errors'])) {
            throw new \RuntimeException($result['message'] ?? 'Yandex Metrika returned no data');
        }

        return $result;
    }

    /**
     * @param  list<int>  $productIds
     * @return array{views: array<int, float>, purchases: array<int, float>, carts: array<int, float>}
     *
     * @throws \RuntimeException
     */
    public function fetchProductMetrics(array $productIds): array
    {
        $views = array_fill_keys($productIds, 0.0);
        $purchases = array_fill_keys($productIds, 0.0);
        $carts = array_fill_keys($productIds, 0.0);
        $counterId = config('services.yandex.counter_id');

        $popularResult = $this->fetch([
            'ids' => $counterId,
            'metrics' => 'ym:s:productImpressionsUniq,ym:s:productPurchasedUniq',
            'dimensions' => 'ym:s:productID',
            'date1' => '30daysAgo',
            'date2' => 'yesterday',
            'sort' => 'ym:s:productID',
            'limit' => 3000,
        ]);

        foreach ($popularResult['data'] ?? [] as $row) {
            $productId = (int) ($row['dimensions'][0]['name'] ?? 0);

            if (!array_key_exists($productId, $views)) {
                continue;
            }

            $views[$productId] = (float) ($row['metrics'][0] ?? 0);
            $purchases[$productId] = (float) ($row['metrics'][1] ?? 0);
        }

        $cartsResult = $this->fetch([
            'ids' => $counterId,
            'metrics' => 'ym:s:productBasketsUniq',
            'dimensions' => 'ym:s:productID',
            'date1' => '7daysAgo',
            'date2' => 'yesterday',
            'sort' => 'ym:s:productID',
            'limit' => 3000,
        ]);

        foreach ($cartsResult['data'] ?? [] as $row) {
            $productId = (int) ($row['dimensions'][0]['name'] ?? 0);

            if (array_key_exists($productId, $carts)) {
                $carts[$productId] = (float) ($row['metrics'][0] ?? 0);
            }
        }

        return [
            'views' => $views,
            'purchases' => $purchases,
            'carts' => $carts,
        ];
    }

    /**
     * @return list<array{url: string, pageviews: int, visits: int}>
     *
     * @throws \RuntimeException
     */
    public function fetchPageUrlStats(int $periodDays, int $limit): array
    {
        $counterId = config('services.yandex.counter_id');
        // Metrika Reporting API uses 1-based offsets (min 1).
        $offset = 1;
        $rows = [];

        do {
            $result = $this->fetch([
                'ids' => $counterId,
                'dimensions' => 'ym:pv:URL',
                'metrics' => 'ym:pv:pageviews,ym:pv:users',
                'date1' => "{$periodDays}daysAgo",
                'date2' => 'yesterday',
                'sort' => '-ym:pv:pageviews',
                'limit' => $limit,
                'offset' => $offset,
            ]);

            foreach ($result['data'] ?? [] as $row) {
                $rows[] = [
                    'url' => (string) ($row['dimensions'][0]['name'] ?? ''),
                    'pageviews' => (int) ($row['metrics'][0] ?? 0),
                    'visits' => (int) ($row['metrics'][1] ?? 0),
                ];
            }

            $totalRows = (int) ($result['total_rows'] ?? 0);
            $offset += $limit;
        } while ($offset <= $totalRows);

        return $rows;
    }
}
