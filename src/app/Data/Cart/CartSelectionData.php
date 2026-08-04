<?php

namespace App\Data\Cart;

use Spatie\LaravelData\Data;

class CartSelectionData extends Data
{
    public function __construct(
        public bool $selected,
    ) {}
}
