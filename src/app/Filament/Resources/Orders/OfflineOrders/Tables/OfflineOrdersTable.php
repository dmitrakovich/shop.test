<?php

namespace App\Filament\Resources\Orders\OfflineOrders\Tables;

use App\Filament\Resources\Products\Products\ProductResource;
use App\Filament\Resources\Users\Users\UserResource;
use App\Helpers\PhoneHelper;
use App\Models\Orders\OfflineOrder;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfflineOrdersTable
{
    /**
     * Relations the table renders.
     *
     * @var list<string>
     */
    public const array EAGER_LOAD = ['product.media', 'product.brand', 'size', 'stock', 'user'];

    /**
     * @param  bool  $withClientColumns  false when the table is shown on the client page itself
     */
    public static function configure(Table $table, bool $withClientColumns = true): Table
    {
        return $table
            ->columns([
                TextColumn::make('sold_at')
                    ->label('Дата продажи')
                    ->dateTime('d.m.y H:i')
                    ->sortable(),
                TextColumn::make('receipt_number')
                    ->label('Чек'),
                ImageColumn::make('product_photo')
                    ->label('Фото')
                    ->state(fn (OfflineOrder $record): ?string => $record->product?->getFirstMediaUrl('default', 'thumb') ?: null)
                    ->imageHeight(60),
                TextColumn::make('sku')
                    ->label('Товар')
                    ->formatStateUsing(fn (OfflineOrder $record): string => trim("{$record->product?->brand?->name} {$record->sku}"))
                    ->description(fn (OfflineOrder $record): string => $record->product ? "код {$record->product_id}" : 'нет на сайте')
                    ->url(fn (OfflineOrder $record): ?string => $record->product
                        ? ProductResource::getUrl('edit', ['record' => $record->product])
                        : null)
                    ->searchable(query: fn (Builder $query, string $search): Builder => self::searchByProduct($query, $search)),
                TextColumn::make('size.name')
                    ->label('Размер')
                    ->placeholder('—'),
                TextColumn::make('count')
                    ->label('Кол-во')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label('Сумма')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BYN'),
                TextColumn::make('stock.internal_name')
                    ->label('Магазин')
                    ->placeholder('—'),
                TextColumn::make('user')
                    ->label('Клиент')
                    ->state(fn (OfflineOrder $record): ?string => $record->user?->getFullName() ?: null)
                    ->url(fn (OfflineOrder $record): ?string => $record->user
                        ? UserResource::getUrl('edit', ['record' => $record->user])
                        : null)
                    ->placeholder('—')
                    ->visible($withClientColumns),
                TextColumn::make('user_phone')
                    ->label('Телефон')
                    ->state(fn (OfflineOrder $record): ?string => $record->getPhone()?->toE164())
                    ->placeholder('—')
                    ->visible($withClientColumns),
                TextColumn::make('returned_at')
                    ->label('Возврат')
                    ->dateTime('d.m.y H:i')
                    ->color('warning')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('stock_id')
                    ->label('Магазин')
                    ->multiple()
                    ->options(fn (): array => Stock::query()
                        ->whereIn('id', OfflineOrder::query()->distinct()->select('stock_id'))
                        ->orderBy('internal_name')
                        ->pluck('internal_name', 'id')
                        ->all())
                    ->native(false),
                TernaryFilter::make('returned_at')
                    ->label('Возврат')
                    ->nullable()
                    ->trueLabel('Только возвраты')
                    ->falseLabel('Без возвратов')
                    ->native(false),
                Filter::make('user_phone')
                    ->schema([
                        TextInput::make('user_phone')
                            ->label('Телефон клиента')
                            ->tel(),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if (filled($data['user_phone'] ?? null)) {
                            PhoneHelper::constrainSearch($query, (string)$data['user_phone'], 'user_phone');
                        }
                    })
                    ->indicateUsing(fn (array $data): ?string => filled($data['user_phone'] ?? null)
                        ? "Телефон {$data['user_phone']}"
                        : null)
                    ->visible($withClientColumns),
                Filter::make('sold_at')
                    ->schema([
                        Fieldset::make()
                            ->label('Дата продажи')
                            ->schema([
                                DatePicker::make('sold_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('sold_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['sold_from'] ?? null) {
                            $query->whereDate('sold_at', '>=', $data['sold_from']);
                        }

                        if ($data['sold_until'] ?? null) {
                            $query->whereDate('sold_at', '<=', $data['sold_until']);
                        }
                    }),
            ])
            ->searchPlaceholder('Код товара или артикул')
            ->recordActions([])
            ->toolbarActions([])
            ->selectable(false);
    }

    /**
     * Exact match by product id, partial match by 1C SKU or by site SKU.
     *
     * @param  Builder<OfflineOrder>  $query
     * @return Builder<OfflineOrder>
     */
    private static function searchByProduct(Builder $query, string $search): Builder
    {
        $search = trim($search);

        return $query->where(function (Builder $query) use ($search): void {
            if (ctype_digit($search)) {
                $query->where('product_id', (int)$search);
            }

            $query
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhereIn(
                    'product_id',
                    Product::withTrashed()->where('sku', 'like', "%{$search}%")->select('id'),
                );
        });
    }
}
