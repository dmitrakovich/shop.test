<?php

namespace App\ElasticSearch\Indices\Product\Enums;

enum ProductSortEnum: string
{
    case Rating = 'rating';
    case Newness = 'newness';
    case PriceUp = 'price-up';
    case PriceDown = 'price-down';
}
