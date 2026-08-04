<?php

namespace App\Enums\Catalog;

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
}
