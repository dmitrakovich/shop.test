<?php

namespace App\Models\Data;

use Carbon\Carbon;

class SaleData
{
    public function __construct(
        public float $price,
        public float $discount,
        public float $discount_percentage,
        public string $label,
        public ?Carbon $end_datetime = null
    ) {}
}
