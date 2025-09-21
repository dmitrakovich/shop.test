<?php

namespace App\Filament\Resources\Registries\DefectiveProducts;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Registries\DefectiveProducts\Pages\ListDefectiveProducts;
use App\Models\AvailableSizes;
use App\Models\DefectiveProduct;
use App\Models\Product;
use App\Models\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DefectiveProductResource extends Resource
{
    protected static ?string $model = DefectiveProduct::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Registers;

    protected static ?string $modelLabel = 'Реестр брака';

    protected static ?string $pluralModelLabel = 'Реестр брака';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'id', function (Builder $query) {
                        $query->whereHas('availableSizes')->with('brand');
                    })
                    ->getOptionLabelFromRecordUsing(fn (Product $record) => $record->nameForAdmin())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('size_id', null);
                        $set('stock_id', null);
                    }),
                Select::make('size_id')
                    ->label('Размер')
                    ->native(false)
                    ->placeholder('Выберите размер')
                    ->disabled(fn (Get $get) => !$get('product_id'))
                    ->options(function (Get $get) {
                        if (!($productId = (int)$get('product_id'))) {
                            return [];
                        }

                        return DB::table('product_attributes')
                            ->join('sizes', 'product_attributes.attribute_id', '=', 'sizes.id')
                            ->where('product_attributes.product_id', $productId)
                            ->where('product_attributes.attribute_type', (new Size())->getMorphClass())
                            ->pluck('sizes.name', 'sizes.id')
                            ->toArray();
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('stock_id', null)),
                Select::make('stock_id')
                    ->label('Склад')
                    ->native(false)
                    ->columnSpanFull()
                    ->placeholder('Выберите склад')
                    ->disabled(fn (Get $get) => !$get('size_id'))
                    ->options(function (Get $get) {
                        if (!($productId = (int)$get('product_id')) || !($sizeId = (int)$get('size_id'))) {
                            return [];
                        }
                        /** @var Collection<int, AvailableSizes> $availableSizes */
                        $availableSizes = AvailableSizes::query()
                            ->where('product_id', $productId)
                            ->where(AvailableSizes::convertSizeIdToField($sizeId), '>', 0)
                            ->get();

                        return $availableSizes->mapWithKeys(
                            fn (AvailableSizes $availableSizes) => [$availableSizes->stock_id => $availableSizes->stock->internal_name]
                        );
                    })
                    ->required(),
                Textarea::make('reason')
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
                TextColumn::make('product')
                    ->label('Товар')
                    ->formatStateUsing(fn (Product $state) => $state->nameForAdmin())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('product_id', 'like', "%{$search}%");
                    }),
                TextColumn::make('size.name')
                    ->label('Размер')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock.internal_name')
                    ->label('Склад')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Причина добавления'),
                TextColumn::make('created_at')
                    ->label('Дата добавления')
                    ->dateTime()
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product.brand']))
            ->searchPlaceholder('Поиск по коду товара')
            ->defaultSort('id', 'desc')
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDefectiveProducts::route('/'),
        ];
    }
}
