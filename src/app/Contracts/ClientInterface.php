<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read \App\Models\Cart|null $cart
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Favorite[] $favorites
 */
interface ClientInterface
{
    /**
     * Get the cart associated with the client.
     */
    public function cart(): HasOne;

    /**
     * Get the favorites associated with the client.
     */
    public function favorites(): HasMany;
}
