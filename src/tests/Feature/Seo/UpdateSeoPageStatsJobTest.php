<?php

namespace Tests\Feature\Seo;

use App\Enums\Seo\SeoPageType;
use App\Jobs\UpdateSeoPageStatsJob;
use App\Models\Seo\SeoPage;
use App\Services\Seo\SeoPageStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateSeoPageStatsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_updates_stats_from_metrika_response(): void
    {
        config([
            'services.yandex.token' => 'test-token',
            'services.yandex.counter_id' => '12345',
            'seo.page_stats.period_days' => 30,
            'seo.page_stats.api_limit' => 100,
            'seo.page_stats.pageviews_weight' => 0.5,
        ]);

        $page = SeoPage::query()->create([
            'page_type' => SeoPageType::Catalog,
            'url' => 'catalog/heel-stiletto/evening/corporate',
            'title' => 'Corporate shoes',
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::response([
                'data' => [
                    [
                        'dimensions' => [['name' => 'https://barocco.by/catalog/heel-stiletto/evening/corporate/']],
                        'metrics' => [120, 45],
                    ],
                ],
                'total_rows' => 1,
            ]),
        ]);

        UpdateSeoPageStatsJob::dispatchSync();

        $page->refresh();

        $this->assertSame(120, $page->pageviews);
        $this->assertSame(45, $page->visits);
        $this->assertEqualsWithDelta(
            SeoPageStatsService::calculateScore(120, 45, 0.5),
            $page->score,
            0.0001,
        );

        Http::assertSent(function (Request $request): bool {
            parse_str((string)parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_starts_with($request->url(), 'https://api-metrika.yandex.ru/stat/v1/data')
                && (int)($query['offset'] ?? 0) === 1
                && (int)($query['limit'] ?? 0) === 100;
        });
    }

    public function test_job_paginates_metrika_with_one_based_offset(): void
    {
        config([
            'services.yandex.token' => 'test-token',
            'services.yandex.counter_id' => '12345',
            'seo.page_stats.api_limit' => 1,
        ]);

        $firstPage = SeoPage::query()->create([
            'page_type' => SeoPageType::Catalog,
            'url' => 'catalog/first',
            'title' => 'First',
        ]);
        $secondPage = SeoPage::query()->create([
            'page_type' => SeoPageType::Catalog,
            'url' => 'catalog/second',
            'title' => 'Second',
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::sequence()
                ->push([
                    'data' => [
                        [
                            'dimensions' => [['name' => 'https://barocco.by/catalog/first/']],
                            'metrics' => [10, 5],
                        ],
                    ],
                    'total_rows' => 2,
                ])
                ->push([
                    'data' => [
                        [
                            'dimensions' => [['name' => 'https://barocco.by/catalog/second/']],
                            'metrics' => [20, 8],
                        ],
                    ],
                    'total_rows' => 2,
                ]),
        ]);

        UpdateSeoPageStatsJob::dispatchSync();

        $firstPage->refresh();
        $secondPage->refresh();

        $this->assertSame(10, $firstPage->pageviews);
        $this->assertSame(20, $secondPage->pageviews);

        Http::assertSentCount(2);
        Http::assertSent(function (Request $request): bool {
            parse_str((string)parse_url($request->url(), PHP_URL_QUERY), $query);

            return (int)($query['offset'] ?? 0) === 1;
        });
        Http::assertSent(function (Request $request): bool {
            parse_str((string)parse_url($request->url(), PHP_URL_QUERY), $query);

            return (int)($query['offset'] ?? 0) === 2;
        });
    }

    public function test_job_matches_catalog_root_with_and_without_trailing_slash(): void
    {
        config([
            'services.yandex.token' => 'test-token',
            'services.yandex.counter_id' => '12345',
        ]);

        $page = SeoPage::query()->create([
            'page_type' => SeoPageType::Catalog,
            'url' => 'catalog',
            'title' => 'Catalog',
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::response([
                'data' => [
                    [
                        'dimensions' => [['name' => 'https://barocco.by/catalog']],
                        'metrics' => [500, 200],
                    ],
                ],
                'total_rows' => 1,
            ]),
        ]);

        UpdateSeoPageStatsJob::dispatchSync();

        $page->refresh();

        $this->assertSame(500, $page->pageviews);
        $this->assertSame(200, $page->visits);
    }

    public function test_job_keeps_existing_stats_when_api_fails(): void
    {
        config([
            'services.yandex.token' => 'test-token',
            'services.yandex.counter_id' => '12345',
        ]);

        $page = SeoPage::query()->create([
            'page_type' => SeoPageType::Catalog,
            'url' => 'catalog/boots',
            'title' => 'Boots',
            'pageviews' => 99,
            'visits' => 33,
            'score' => 5.5,
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::response([
                'errors' => [['message' => 'API error']],
            ], 500),
        ]);

        UpdateSeoPageStatsJob::dispatchSync();

        $page->refresh();

        $this->assertSame(99, $page->pageviews);
        $this->assertSame(33, $page->visits);
        $this->assertEqualsWithDelta(5.5, $page->score, 0.0001);
    }

    public function test_job_sets_zero_stats_for_pages_missing_in_metrika_response(): void
    {
        config([
            'services.yandex.token' => 'test-token',
            'services.yandex.counter_id' => '12345',
        ]);

        $page = SeoPage::query()->create([
            'page_type' => SeoPageType::Catalog,
            'url' => 'catalog/empty',
            'title' => 'Empty',
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::response([
                'data' => [],
                'total_rows' => 0,
            ]),
        ]);

        UpdateSeoPageStatsJob::dispatchSync();

        $page->refresh();

        $this->assertSame(0, $page->pageviews);
        $this->assertSame(0, $page->visits);
        $this->assertEqualsWithDelta(0.0, $page->score, 0.0001);
    }

    public function test_job_fails_when_there_are_no_seo_pages(): void
    {
        config([
            'services.yandex.token' => 'test-token',
            'services.yandex.counter_id' => '12345',
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::response([
                'data' => [],
                'total_rows' => 0,
            ]),
        ]);

        UpdateSeoPageStatsJob::dispatchSync();

        $this->assertSame(0, SeoPage::query()->count());
    }
}
