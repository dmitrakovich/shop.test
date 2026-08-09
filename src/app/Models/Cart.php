<?php

namespace App\Models;

use App\Enums\Config\ConfigKey;
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
 */
class Cart extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the device associated with the cart.
     *
     * @return BelongsTo<UserDevice, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class);
    }

    /**
     * Get the user associated with the cart.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the promocode associated with the cart.
     *
     * @return BelongsTo<Promocode, $this>
     */
    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    /**
     * Cart's items
     *
     * @return HasMany<CartData, $this>
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
     * Get available items selected for checkout.
     *
     * @return Collection<int, CartData>
     */
    public function selectedAvailableItems(): Collection
    {
        return $this->availableItems()->filter(fn (CartData $item) => $item->isSelected());
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
     * Get the total old price of selected available items.
     *
     * @todo refactor applying sale
     */
    public function getTotalOldPrice(): float
    {
        Sale::applyToCart($this);

        $price = 0;
        foreach ($this->selectedAvailableItems() as $item) {
            $price += ($item->product->getOldPrice() * $item->count);
        }

        return $price;
    }

    /**
     * Get selected available items cart price
     *
     * @todo refactor applying sale
     */
    public function getTotalPrice(?string $currencyCode = null): float
    {
        Sale::applyToCart($this);

        $price = 0;
        foreach ($this->selectedAvailableItems() as $item) {
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
            if (!$item->isSelected()) {
                $item->update(['selected' => true]);
            }
        } else {
            $this->items()->create([
                'product_id' => $productId,
                'count' => 1,
                'size_id' => $sizeId,
                'selected' => true,
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
     * Set selection flag for one cart item.
     */
    public function setItemSelected(int $itemId, bool $selected): self
    {
        $this->items()->where('id', $itemId)->update(['selected' => $selected]);

        return $this->refreshItems();
    }

    /**
     * Select or deselect all cart items.
     */
    public function setAllSelected(bool $selected): self
    {
        $this->items()->update(['selected' => $selected]);

        return $this->refreshItems();
    }

    /**
     * Remove all selected cart items.
     */
    public function removeSelectedItems(): self
    {
        $this->items()->where('selected', true)->delete();

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
     * Clear selected available items from the shopping cart (used after checkout).
     * Unavailable selected rows are kept — they were not ordered.
     */
    public function clearSelected(): void
    {
        $itemIds = $this->selectedAvailableItems()->pluck('id');
        $this->items()->whereIn('id', $itemIds)->delete();
        $this->refreshItems();
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
        return $this->getTotalPrice() >= Config::value(ConfigKey::Installment, 'min_price');
    }

    /**
     * Check if the cart has an applied promocode.
     */
    public function hasPromocode(): bool
    {
        return (bool)$this->promocode;
    }

    /**
     * Check if the cart has available items.
     */
    public function hasAvailableItems(): bool
    {
        return $this->availableItems()->isNotEmpty();
    }

    /**
     * Check if the cart has selected available items for checkout.
     */
    public function hasSelectedAvailableItems(): bool
    {
        return $this->selectedAvailableItems()->isNotEmpty();
    }

    /**
     * Check if the cart is suspicious.
     */
    public function isSuspicious(): bool
    {
        return $this->getTotalPrice() > 1_000_000;
    }
}
