<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\SeoPageStatsService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeoPageStatsServiceTest extends TestCase
{
    public function test_calculate_score_uses_logarithmic_formula(): void
    {
        $score = SeoPageStatsService::calculateScore(pageviews: 100, visits: 50, pageviewsWeight: 0.5);

        $this->assertEqualsWithDelta(log(1 + 50 + (0.5 * 100)), $score, 0.0001);
    }

    public function test_calculate_score_returns_zero_for_empty_metrics(): void
    {
        $score = SeoPageStatsService::calculateScore(pageviews: 0, visits: 0, pageviewsWeight: 0.5);

        $this->assertEqualsWithDelta(0.0, $score, 0.0001);
    }

    #[DataProvider('metrikaUrlProvider')]
    public function test_normalize_metrika_url(string $input, string $expected): void
    {
        $this->assertSame($expected, SeoPageStatsService::normalizeMetrikaUrl($input));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function metrikaUrlProvider(): array
    {
        return [
            'full url' => ['https://barocco.by/catalog/shoes', 'catalog/shoes'],
            'path only' => ['/catalog/shoes', 'catalog/shoes'],
            'with query' => ['https://barocco.by/catalog/shoes?color=red', 'catalog/shoes?color=red'],
            'relative path' => ['catalog/shoes', 'catalog/shoes'],
        ];
    }
}
