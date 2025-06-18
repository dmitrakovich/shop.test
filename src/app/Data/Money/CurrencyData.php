<?php

namespace App\Data\Money;

use App\Enums\CurrencyCode;
use Spatie\LaravelData\Data;

class CurrencyData extends Data
{
    public CurrencyCode $currency;
}
