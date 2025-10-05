<?php

namespace App\Filament\Resources\Products\Products\RelationManagers;

use App\Filament\Actions\Product\AddToGroupAction;
use App\Filament\Actions\Product\RemoveFromGroupAction;
use App\Filament\Resources\Products\Products\ProductResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ProductsFromGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'productsFromGroup';

    protected static ?string $relatedResource = ProductResource::class;

    protected static ?string $title = 'Группа товаров';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AddToGroupAction::make(),
                RemoveFromGroupAction::make(),
            ])
            ->searchable(false);
    }
}
