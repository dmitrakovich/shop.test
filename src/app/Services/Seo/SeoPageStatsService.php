<?php

namespace App\Services\Seo;

use App\Models\Seo\SeoPage;
use App\Services\Api\Yandex\MetrikaService;
use Illuminate\Support\Facades\DB;
use League\Uri\Uri;

class SeoPageStatsService
{
    public function __construct(
        private MetrikaService $metrika,
    ) {}

    /**
     * @return array<string, array{pageviews: int, visits: int}>
     *
     * @throws \RuntimeException
     */
    public function fetchMetricsByUrl(): array
    {
        $periodDays = (int) config('seo.page_stats.period_days');
        $limit = (int) config('seo.page_stats.api_limit');

        $rows = $this->metrika->fetchPageUrlStats($periodDays, $limit);
        $mapped = [];

        foreach ($rows as $row) {
            $url = self::normalizeMetrikaUrl($row['url']);

            if ($url === '') {
                continue;
            }

            if (isset($mapped[$url])) {
                $mapped[$url]['pageviews'] += $row['pageviews'];
                $mapped[$url]['visits'] += $row['visits'];

                continue;
            }

            $mapped[$url] = [
                'pageviews' => $row['pageviews'],
                'visits' => $row['visits'],
            ];
        }

        return $mapped;
    }

    /**
     * @throws \RuntimeException
     */
    public function syncStats(): int
    {
        $metricsByUrl = $this->fetchMetricsByUrl();
        $pageviewsWeight = (float) config('seo.page_stats.pageviews_weight');
        $updates = [];

        foreach (SeoPage::query()->pluck('url', 'id') as $id => $url) {
            $metrics = $metricsByUrl[$url] ?? ['pageviews' => 0, 'visits' => 0];

            $updates[(int) $id] = [
                'pageviews' => $metrics['pageviews'],
                'visits' => $metrics['visits'],
                'score' => self::calculateScore($metrics['pageviews'], $metrics['visits'], $pageviewsWeight),
            ];
        }

        if ($updates === []) {
            return 0;
        }

        DB::transaction(function () use ($updates): void {
            $this->updateStats($updates);
        });

        return count($updates);
    }

    /**
     * @param  array<int, array{pageviews: int, visits: int, score: float}>  $updates
     */
    private function updateStats(array $updates): void
    {
        foreach (array_chunk($updates, 500, true) as $chunk) {
            $pageviewsCases = '';
            $visitsCases = '';
            $scoreCases = '';
            $pageIds = [];

            foreach ($chunk as $id => $values) {
                $pageIds[] = (int) $id;
                $pageviewsCases .= "WHEN id = {$id} THEN {$values['pageviews']} ";
                $visitsCases .= "WHEN id = {$id} THEN {$values['visits']} ";
                $scoreCases .= "WHEN id = {$id} THEN {$values['score']} ";
            }

            $ids = implode(',', $pageIds);

            DB::statement(<<<SQL
                UPDATE seo_pages SET
                    pageviews = (CASE {$pageviewsCases} ELSE pageviews END),
                    visits = (CASE {$visitsCases} ELSE visits END),
                    score = (CASE {$scoreCases} ELSE score END)
                WHERE id IN ({$ids})
                SQL);
        }
    }

    public static function calculateScore(int $pageviews, int $visits, float $pageviewsWeight): float
    {
        return log(1 + $visits + ($pageviewsWeight * $pageviews));
    }

    public static function normalizeMetrikaUrl(string $url): string
    {
        $uri = Uri::parse($url);

        if ($uri === null) {
            return ltrim($url, '/');
        }

        $path = ltrim($uri->getPath(), '/');
        $query = $uri->getQuery();

        if (filled($query)) {
            $path .= '?' . $query;
        }

        return $path;
    }
}
