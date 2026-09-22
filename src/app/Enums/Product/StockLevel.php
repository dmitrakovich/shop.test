<?php

namespace App\Enums\Product;

/**
 * Stock richness for Google feed custom_label_0.
 */
enum StockLevel: string
{
    case Full = 'full';
    case Mid = 'mid';
    case Low = 'low';
}
