<?php

namespace App\Http\Resources\Price;

use App\Facades\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->resource,
            'formatted_price' => Currency::format($this->resource),
            'currency_code' => Currency::getCurrentCurrency()->code,
        ];
    }
}
