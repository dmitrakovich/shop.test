<?php

namespace App\Http\Resources\Product;

use App\Enums\StockTypeEnum;
use App\Facades\Currency;
use App\Models\AvailableSizes;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->load([
            'availableSizes' => fn (Builder $query) => $query->whereRelation('stock', 'type', StockTypeEnum::SHOP),
            'availableSizes.stock.city',
        ]);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'color_txt' => $this->color_txt,
            'fabric_top_txt' => $this->fabric_top_txt,
            'fabric_inner_txt' => $this->fabric_inner_txt,
            'fabric_insole_txt' => $this->fabric_insole_txt,
            'fabric_outsole_txt' => $this->fabric_outsole_txt,
            'heel_txt' => $this->heel_txt,
            'bootleg_height_txt' => $this->bootleg_height_txt,
            'description' => $this->description,
            'rating' => $this->rating,
            'key_features' => $this->key_features,

            'prices' => [
                'price' => $this->getFinalPrice(),
                'old_price' => $this->getFinalOldPrice(),
                'formatted_price' => $this->getFormattedPrice(),
                'formatted_old_price' => $this->getFormattedOldPrice(),
                'has_discount' => $this->hasDiscount(),
                'sale_percentage' => $this->getSalePercentage(),
                'sales' => $this->getSales(),
                'currency' => Currency::getCurrentCurrency(),
            ],

            'is_favorite' => $this->isFavorite(),
            'is_installment_available' => $this->availableInstallment(),
            'is_new' => $this->isNew(),
            'short_name' => $this->shortName(),
            'trashed' => $this->trashed(),

            'brand' => new BrandResource($this->brand),
            'category' => new CategoryResource($this->category),
            'country_of_origin' => new CountryOfOriginResource($this->countryOfOrigin),
            'media' => MediaResource::collection($this->getMedia()),
            'season' => new SeasonResource($this->season),
            'sizes' => SizeResource::collection($this->sizes),
            'stocks' => StockResource::collection($this->availableSizes->sortBy(
                fn (AvailableSizes $availableSizes) => $availableSizes->stock->site_sorting
            )),
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
