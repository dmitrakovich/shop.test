<?php

namespace App\Models;

use App\Services\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\Promo\Promocode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $promocode_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Promo\Promocode|null $promocode
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartData[] $items
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Cart extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['promocode_id'];

    /**
     * Get the promocode associated with the cart.
     */
    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    /**
     * Cart's items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartData::class);
    }

    /**
     * Get the available items in the shopping cart.
     */
    public function availableItems(): Collection
    {
        return $this->items->filter(fn (CartData $item) => $item->isAvailable());
    }

    /**
     * Get the total count of items in the cart.
     */
    public function itemsCount(): int
    {
        $counter = 0;
        foreach ($this->items as $item) {
            $counter += $item->count;
        }

        return $counter;
    }

    /**
     * Get the total old price of items in the cart.
     */
    public function getTotalOldPrice(): float
    {
        $price = 0;
        foreach ($this->availableItems() as $item) {
            $price += ($item->product->getOldPrice() * $item->count);
        }

        return $price;
    }

    /**
     * Get all items cart price
     */
    public function getTotalPrice(?string $currencyCode = null): float
    {
        $price = 0;
        foreach ($this->availableItems() as $item) {
            $price += ($item->product->getPrice($currencyCode) * $item->count);
        }

        return $price;
    }

    /**
     * Add item to cart
     */
    public function addItem(int $productId, int $sizeId): void
    {
        $this->createIfNotExists();

        $item = $this->items
            ->where('product_id', $productId)
            ->where('size_id', $sizeId)
            ->first();

        if (isset($item)) {
            $item->increment('count');
        } else {
            $this->items()->create([
                'product_id' => $productId,
                'count' => 1,
                'size_id' => $sizeId,
            ]);
        }

        $this->refreshItems();
    }

    /**
     * Refresh car items
     */
    protected function refreshItems(): void
    {
        $this->load('items');
    }

    /**
     * Create a new cart if it doesn't exist.
     */
    public function createIfNotExists(): self
    {
        if (!$this->exists) {
            $this->save();
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $user->cart_token = $this->id;
                $user->save();
            } else {
                Cookie::queue(cookie('cart_token', $this->id, 60 * 24 * 30, '/'));
            }
        }

        return $this;
    }

    /**
     * Clear items from the shopping cart.
     */
    public function clear($onlyAvailable = false): void
    {
        if ($onlyAvailable) {
            $itemIds = $this->availableItems()->pluck('id');
            $this->items()->whereIn('id', $itemIds)->delete();
        } else {
            $this->items()->delete();
        }
    }

    /**
     * Get the current instance of the cart.
     */
    public function getCart(): self
    {
        return app(CartService::class)->prepareCart($this);
    }

    /**
     * Check if the cart total price meets the minimum installment price.
     */
    public function availableInstallment(): bool
    {
        return $this->getTotalPrice() >= Config::findCacheable('installment')['min_price'];
    }
}
