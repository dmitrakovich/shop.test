<?php

namespace App\Enums\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\Status;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

enum CatalogFacetName: string
{
    case Categories = 'categories';
    case Statuses = 'statuses';
    case Fabrics = 'fabrics';
    case Collections = 'collections';
    case Sizes = 'sizes';
    case Colors = 'colors';
    case Heels = 'heels';
    case Seasons = 'seasons';
    case Styles = 'styles';
    case Tags = 'tags';
    case Brands = 'brands';

    /**
     * Filterable Eloquent model for this facet.
     *
     * @return class-string<Model>
     */
    public function model(): string
    {
        return match ($this) {
            self::Categories => Category::class,
            self::Statuses => Status::class,
            self::Fabrics => Fabric::class,
            self::Collections => ProductCollection::class,
            self::Sizes => Size::class,
            self::Colors => Color::class,
            self::Heels => Heel::class,
            self::Seasons => Season::class,
            self::Styles => Style::class,
            self::Tags => Tag::class,
            self::Brands => Brand::class,
        };
    }
}
