<?php

namespace App\Enums\Product;

use App\Models\ProductAttributes\Status;
use App\Models\Season;
use App\Models\Url;

enum ProductRatingColumn: string
{
    case Rating = 'rating';
    case SeasonRating = 'season_rating';
    case SaleRating = 'sale_rating';
    case NewnessRating = 'newness_rating';
    case SeasonNewnessRating = 'season_newness_rating';

    /**
     * Popularity score for the active catalog context.
     *
     * @param  array<string, array<string, Url>>  $filters
     */
    public static function fromFilters(array $filters): self
    {
        if (isset($filters[Status::class]['st-sale'])) {
            return self::SaleRating;
        }

        if (self::hasActualSeason($filters)) {
            return self::SeasonRating;
        }

        return self::Rating;
    }

    /**
     * Newness score for the active catalog context.
     *
     * Sale listings keep the catalog newness score: there is no sale-specific newness rating.
     *
     * @param  array<string, array<string, Url>>  $filters
     */
    public static function newnessFromFilters(array $filters): self
    {
        if (self::hasActualSeason($filters)) {
            return self::SeasonNewnessRating;
        }

        return self::NewnessRating;
    }

    /**
     * @param  array<string, array<string, Url>>  $filters
     */
    private static function hasActualSeason(array $filters): bool
    {
        foreach ($filters[Season::class] ?? [] as $url) {
            $season = $url->filters;

            if ($season instanceof Season && $season->is_actual) {
                return true;
            }
        }

        return false;
    }
}
