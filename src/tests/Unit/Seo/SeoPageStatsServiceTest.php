<?php

namespace Tests\Unit\Seo;

use App\Models\Seo\SeoPage;
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

    #[DataProvider('urlKeyProvider')]
    public function test_url_key_normalizes_urls_for_lookup(string $input, string $expected): void
    {
        $this->assertSame($expected, SeoPage::urlKey($input));
    }

    public function test_url_key_treats_catalog_root_variants_as_same_key(): void
    {
        $this->assertSame(
            SeoPage::urlKey('catalog'),
            SeoPage::urlKey('https://barocco.by/catalog/'),
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function urlKeyProvider(): array
    {
        return [
            'full url' => ['https://barocco.by/catalog/shoes', 'catalog/shoes'],
            'path only' => ['/catalog/shoes', 'catalog/shoes'],
            'trailing slash' => ['https://barocco.by/catalog/shoes/', 'catalog/shoes'],
            'catalog root with slash' => ['catalog/', 'catalog'],
            'catalog root without slash' => ['https://barocco.by/catalog', 'catalog'],
            'db url with nested path' => ['catalog/heel-stiletto/evening/corporate', 'catalog/heel-stiletto/evening/corporate'],
            'metrika url with nested path' => ['https://barocco.by/catalog/heel-stiletto/evening/corporate/', 'catalog/heel-stiletto/evening/corporate'],
            'ignores query string' => ['https://barocco.by/catalog/shoes?utm_source=google', 'catalog/shoes'],
        ];
    }
}
