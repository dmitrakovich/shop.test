<?php

namespace App\Filament\Resources\Registries;

use App\Filament\Resources\Registries\DefectiveProductResource\Pages;
use App\Models\AvailableSizes;
use App\Models\DefectiveProduct;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DefectiveProductResource extends Resource
{
    protected static ?string $model = DefectiveProduct::class;

    protected static ?string $navigationGroup = 'registries';

    protected static ?string $modelLabel = 'Реестр брака';

    protected static ?string $pluralModelLabel = 'Реестр брака';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'id', function (Builder $query) {
                        $query->whereHas('sizes')->with('brand');
                    })
                    ->getOptionLabelFromRecordUsing(fn (Product $record) => $record->nameForAdmin())
                    ->searchable()
                    ->required()
                    ->live(),
                Forms\Components\Select::make('size_id')
                    ->label('Размер')
                    ->native(false)
                    ->placeholder('Выберите размер')
                    ->disabled(fn (Forms\Get $get) => !$get('product_id'))
                    ->options(function (Forms\Get $get) {
                        if (!($productId = (int)$get('product_id'))) {
                            return [];
                        }
                        /** @var \App\Models\Product|null $product */
                        $product = Product::withTrashed()->find($productId);

                        return $product?->sizes->pluck('name', 'id')->toArray() ?? [];
                    })
                    // ->live()
                    ->required(),
                Forms\Components\Select::make('stock_id')
                    ->label('Склад')
                    ->native(false)
                    ->columnSpanFull()
                    ->placeholder('Выберите склад')
                    // ->disabled(fn (Forms\Get $get) => !$get('size_id'))
                    ->options(function (Forms\Get $get) {
                        return Stock::query()
                            ->where('check_availability', true)
                            ->pluck('internal_name', 'id');

                        // if (!($productId = (int)$get('product_id')) || !($sizeId = (int)$get('size_id'))) {
                        //     return [];
                        // }
                        // /** @var Collection<int, AvailableSizes> $availableSizes */
                        // $availableSizes = AvailableSizes::query()
                        //     ->where('product_id', $productId)
                        //     ->where(AvailableSizes::convertSizeIdToField($sizeId), '>', 0)
                        //     ->get();

                        // return $availableSizes->mapWithKeys(
                        //     fn (AvailableSizes $availableSizes, int $key) => [$key => $availableSizes->stock->internal_name]
                        // );
                    })
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Причина')
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Бракованные товары отсутствуют')
            ->columns([
                Tables\Columns\TextColumn::make('product')
                    ->label('Товар')
                    ->formatStateUsing(fn (Product $state) => $state->nameForAdmin())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('product_id', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('size.name')
                    ->label('Размер')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock.internal_name')
                    ->label('Склад')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Причина добавления'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата добавления')
                    ->dateTime()
                    ->sortable(),
            ])
            ->searchPlaceholder('Поиск по коду товара')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDefectiveProducts::route('/'),
        ];
    }
}
