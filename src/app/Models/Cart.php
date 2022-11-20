<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class Cart extends Model
{
    use HasFactory;

    /**
     * Инициализация корзины
     *
     * @return $this
     */
    public function setCart()
    {
        $cartId = Auth::user() ? Auth::user()->cart_token : Cookie::get('cart_token');

        return self::findOrNew($cartId);
    }

    /**
     * Содержимое корзины
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(CartData::class);
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
        foreach ($this->items as $item) {
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
        foreach ($this->items as $item) {
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
     * Очистить содержимое корзины
     */
    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Получить содержимое корзины
     *
     * @return $this
     */
    public function withData()
    {
        $this->load('items');
        $this->items->load('product');

        foreach ($this->items as $key => $item) {
            if (empty($item->product)) {
                $item->delete();
                $this->items->forget($key);
            }
        }

        $this->items->load('size:id,name');

        return $this;
    }
}
