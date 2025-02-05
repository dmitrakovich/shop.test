<?php

namespace App\Http\Resources\Cart;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @return void
     */
    public function __construct(Cart $resource)
    {
        parent::__construct(
            app(CartService::class)->prepareCart($resource)
        );
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->getTotalPrice(),
            'total_old_price' => $this->getTotalOldPrice(),
            'total_price_without_user_sale' => $this->getTotalPriceWithoutUserSale(),
            'items' => CartItemResource::collection($this->items),
        ];
    }
}
