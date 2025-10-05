<?php

namespace App\Filament\Resources\Products\Products\Pages;

use App\Events\Products\ProductUpdated;
use App\Filament\Actions\Product\PromtAction;
use App\Filament\Resources\Products\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PromtAction::make(),
            Action::make('open')
                ->label('Открыть страницу товара')
                ->icon(Heroicon::ArrowTopRightOnSquare)
                ->url(fn (Product $record) => route('product.show', $record->slug))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        event(new ProductUpdated($this->getRecord()));
    }
}
