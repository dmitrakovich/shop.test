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

    /**
     * @param  array<string, array<string, Url>>  $filters
     */
    public static function fromFilters(array $filters): self
    {
        if (isset($filters[Status::class]['st-sale'])) {
            return self::SaleRating;
        }

        foreach ($filters[Season::class] ?? [] as $url) {
            $season = $url->filters;

            if ($season instanceof Season && $season->is_actual) {
                return self::SeasonRating;
            }
        }

        return self::Rating;
    }
}
