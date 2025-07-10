<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $code currency code 3 symbol (ISO 4217)
 * @property string $country country code 2 symbol (ISO 3166-1)
 * @property float $rate
 * @property int $decimals
 * @property string $symbol
 * @property string|null $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Currency extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';
}
