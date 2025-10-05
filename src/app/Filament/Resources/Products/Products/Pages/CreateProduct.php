<?php

namespace App\Filament\Resources\Products\Products\Pages;

use App\Events\Products\ProductCreated;
use App\Filament\Actions\Product\PromtAction;
use App\Filament\Resources\Products\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PromtAction::make(),
        ];
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Product $product */
        $product = $this->getRecord();

        event(new ProductCreated($product));
    }
}
