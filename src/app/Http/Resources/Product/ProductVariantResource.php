<?php

namespace App\Http\Resources\Product;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'colors' => $this->colors->map(fn (Color $color) => [
                'id' => $color->id,
                'value' => $color->value,
            ]),
        ];
    }
}
