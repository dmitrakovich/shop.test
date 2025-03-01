<?php

namespace App\Data\Order;

use App\Data\Casts\ModelCast;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class OneClickOrderData extends Data
{
    #[MapInputName('product_id')]
    #[WithCast(ModelCast::class, modelClass: Product::class)]
    public Product $product;

    /** @var Collection|Size[] */
    #[MapInputName('size_ids')]
    #[WithCast(ModelCast::class, modelClass: Size::class)]
    public Collection $sizes;
}
