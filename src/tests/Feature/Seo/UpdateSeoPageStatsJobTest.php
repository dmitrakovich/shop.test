<?php

namespace Tests\Feature\Seo;

use App\Enums\Seo\SeoPageType;
use App\Jobs\UpdateSeoPageStatsJob;
use App\Models\Seo\SeoPage;
use App\Services\Seo\SeoPageStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'url' => 'catalog/shoes',
            'title' => 'Shoes',
        ]);

        Http::fake([
            'api-metrika.yandex.ru/*' => Http::response([
                'data' => [
                    [
                        'dimensions' => [['name' => 'https://barocco.by/catalog/shoes']],
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
}
