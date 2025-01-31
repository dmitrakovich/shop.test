<?php

namespace App\Models;

use App\Facades\Device;
use App\Facades\Sale;
use App\Models\Promo\Promocode;
use App\Models\User\Device as UserDevice;
use App\Models\User\User;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int|null $device_id
 * @property int|null $user_id
 * @property int|null $promocode_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User\Device|null $device
 * @property-read \App\Models\User\User|null $user
 * @property-read \App\Models\Promo\Promocode|null $promocode
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartData[] $items
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Cart extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the device associated with the cart.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class);
    }

    /**
     * Get the user associated with the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
     *
     * @return Collection|CartData[]
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
     *
     * @todo refactor applying sale
     */
    public function getTotalOldPrice(): float
    {
        Sale::applyToCart($this);

        $price = 0;
        foreach ($this->availableItems() as $item) {
            $price += ($item->product->getOldPrice() * $item->count);
        }

        return $price;
    }

    /**
     * Get all items cart price
     *
     * @todo refactor applying sale
     */
    public function getTotalPrice(?string $currencyCode = null): float
    {
        Sale::applyToCart($this);

        $price = 0;
        foreach ($this->availableItems() as $item) {
            $price += ($item->product->getPrice($currencyCode) * $item->count);
        }

        return $price;
    }

    /**
     * Get all items cart price without user sale
     *
     * @todo refactor applying sale
     */
    public function getTotalPriceWithoutUserSale(?string $currencyCode = null): float
    {
        Sale::disableUserSale();

        $price = $this->getTotalPrice($currencyCode);

        Sale::enableUserSale();
        Sale::applyToCart($this);

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
     * Remove a cart item by its ID.
     */
    public function removeItemById(int $id): self
    {
        $this->items()->where('id', $id)->delete();

        return $this->refreshItems();
    }

    /**
     * Refresh car items
     */
    protected function refreshItems(): self
    {
        return $this->load('items');
    }

    /**
     * Create a new cart if it doesn't exist.
     */
    public function createIfNotExists(): self
    {
        if (!$this->exists) {
            $this->device_id = Device::id();
            $this->user_id = Auth::id();
            $this->save();
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
     * Clear the applied promocode from the cart.
     */
    public function clearPromocode(): void
    {
        $this->update(['promocode_id' => null]);
        $this->unsetRelation('promocode');
    }

    /**
     * Check if the cart total price meets the minimum installment price.
     */
    public function availableInstallment(): bool
    {
        return $this->getTotalPrice() >= Config::findCacheable('installment')['min_price'];
    }

    /**
     * Check if the cart has an applied promocode.
     */
    public function hasPromocode(): bool
    {
        return (bool)$this->promocode;
    }
}
