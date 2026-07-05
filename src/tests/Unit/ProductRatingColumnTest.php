<?php

namespace Tests\Unit;

use App\Enums\Product\ProductRatingColumn;
use App\Enums\Product\ProductSort;
use App\Models\Product;
use App\Models\ProductAttributes\Status;
use App\Models\Season;
use App\Models\Url;
use Tests\TestCase;

class ProductRatingColumnTest extends TestCase
{
    public function test_from_filters_returns_rating_by_default(): void
    {
        $this->assertSame(
            ProductRatingColumn::Rating,
            ProductRatingColumn::fromFilters([]),
        );
    }

    public function test_from_filters_returns_sale_rating_when_st_sale_filter_is_active(): void
    {
        $status = $this->makeStatus('st-sale');

        $filters = [
            Status::class => [
                'st-sale' => $this->makeFilterUrl($status),
            ],
        ];

        $this->assertSame(
            ProductRatingColumn::SaleRating,
            ProductRatingColumn::fromFilters($filters),
        );
    }

    public function test_from_filters_returns_season_rating_when_actual_season_filter_is_active(): void
    {
        $season = $this->makeSeason('actual-season', isActual: true);

        $filters = [
            Season::class => [
                $season->slug => $this->makeFilterUrl($season),
            ],
        ];

        $this->assertSame(
            ProductRatingColumn::SeasonRating,
            ProductRatingColumn::fromFilters($filters),
        );
    }

    public function test_from_filters_prefers_sale_rating_over_season_rating(): void
    {
        $status = $this->makeStatus('st-sale');
        $season = $this->makeSeason('actual-season', isActual: true);

        $filters = [
            Status::class => [
                'st-sale' => $this->makeFilterUrl($status),
            ],
            Season::class => [
                $season->slug => $this->makeFilterUrl($season),
            ],
        ];

        $this->assertSame(
            ProductRatingColumn::SaleRating,
            ProductRatingColumn::fromFilters($filters),
        );
    }

    public function test_from_filters_ignores_non_actual_season_filter(): void
    {
        $season = $this->makeSeason('old-season', isActual: false);

        $filters = [
            Season::class => [
                $season->slug => $this->makeFilterUrl($season),
            ],
        ];

        $this->assertSame(
            ProductRatingColumn::Rating,
            ProductRatingColumn::fromFilters($filters),
        );
    }

    public function test_scope_sorting_uses_sale_rating_column_when_st_sale_filter_is_active(): void
    {
        $status = $this->makeStatus('st-sale');

        $sql = Product::query()
            ->sorting(ProductSort::Rating, [
                Status::class => [
                    'st-sale' => $this->makeFilterUrl($status),
                ],
            ])
            ->toSql();

        $this->assertStringContainsString('order by `sale_rating` desc', strtolower($sql));
    }

    public function test_scope_sorting_uses_season_rating_column_when_actual_season_filter_is_active(): void
    {
        $season = $this->makeSeason('actual-season', isActual: true);

        $sql = Product::query()
            ->sorting(ProductSort::Rating, [
                Season::class => [
                    $season->slug => $this->makeFilterUrl($season),
                ],
            ])
            ->toSql();

        $this->assertStringContainsString('order by `season_rating` desc', strtolower($sql));
    }

    private function makeStatus(string $slug): Status
    {
        return new Status([
            'id' => 1,
            'slug' => $slug,
            'name' => 'Sale',
        ]);
    }

    private function makeSeason(string $slug, bool $isActual): Season
    {
        return new Season([
            'id' => 1,
            'slug' => $slug,
            'name' => 'Season',
            'is_actual' => $isActual,
        ]);
    }

    private function makeFilterUrl(Season|Status $filter): Url
    {
        return (new Url([
            'slug' => $filter->slug,
            'model_type' => $filter::class,
            'model_id' => $filter->id,
        ]))->setRelation('filters', $filter);
    }
}
