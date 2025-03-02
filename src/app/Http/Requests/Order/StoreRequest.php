<?php

namespace App\Http\Requests\Order;

use App\Enums\Order\OrderMethod;
use Deliveries\ShopPvz;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $utm = json_decode($this->cookie('utm'), true);

        $this->merge([
            'order_method' => $this->getOrderMethod()->value,
            'stock_id' => $this->getStockId(),
            'size_ids' => array_keys($this->input('sizes', [])),

            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content' => $utm['utm_content'] ?? null,
            'utm_term' => $utm['utm_term'] ?? null,
        ]);
    }

    /**
     * Get order method
     */
    protected function getOrderMethod(): OrderMethod
    {
        return $this->order_method
            ?? ($this->has(['product_id', 'sizes']) ? OrderMethod::ONECLICK : null)
            ?? OrderMethod::DEFAULT;
    }

    /**
     * Check is this order made in one click
     */
    public function isOneClick(): bool
    {
        return $this->input('order_method') === OrderMethod::ONECLICK->value;
    }

    /**
     * Get the stock ID if the delivery method is ShopPvz.
     */
    public function getStockId(): ?int
    {
        if ($this->integer('delivery_id') === ShopPvz::ID) {
            return $this->integer('stock_id');
        }

        return null;
    }
}
