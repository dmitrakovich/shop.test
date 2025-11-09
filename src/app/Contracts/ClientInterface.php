<?php

namespace App\Contracts;

use App\Models\Cart;
use App\Models\Favorite;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Cart|null $cart
 * @property-read Collection<int, Favorite> $favorites
 *
 * @template TClient of Model
 */
interface ClientInterface
{
    /**
     * Get the cart associated with the client.
     *
     * @return HasOne<Cart, TClient>
     */
    public function cart(): HasOne;

    /**
     * Get the favorites associated with the client.
     *
     * @return HasMany<Favorite, TClient>
     */
    public function favorites(): HasMany;

    /**
     * Get the user model associated with the client.
     */
    public function getUser(): ?User;
}
