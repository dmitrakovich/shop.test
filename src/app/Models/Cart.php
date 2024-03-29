<?php

namespace App\Models;

use App\Services\CartService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * @property int $id
 * @property int|null $promocode_id
 * @property bool $cancel_promocode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartData[] $items
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Cart extends Model
{
    use HasFactory;

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
     * Количество товаров в корзине
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
     * Получить общую стоимость товаров в корзине
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
     * Создать корзину, если она еще не создана
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
     * Check min installmnet price
     */
    public function availableInstallment(): bool
    {
        return $this->getTotalPrice() >= Config::findCacheable('installment')['min_price'];
    }
}
