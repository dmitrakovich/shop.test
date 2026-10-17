<?php

namespace App\Filament\Resources\Inventory\InventoryLogs\Tables;

use App\Enums\Product\InventoryLogAction;
use App\Filament\Resources\Products\Products\ProductResource;
use App\Models\Logs\InventoryLog;
use App\Models\Product;
use App\Models\Size;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryLogsTable
{
    /**
     * @param  bool  $withProductColumns  false when the table is shown on the product page itself
     */
    public static function configure(Table $table, bool $withProductColumns = true): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                ImageColumn::make('product_photo')
                    ->label('Фото')
                    ->state(fn (InventoryLog $record): ?string => $record->product?->getFirstMediaUrl('default', 'thumb') ?: null)
                    ->imageHeight(60)
                    ->visible($withProductColumns),
                TextColumn::make('product_id')
                    ->label('Товар')
                    ->formatStateUsing(fn (InventoryLog $record): string => self::getProductTitle($record))
                    ->description(fn (InventoryLog $record): string => self::getProductDescription($record))
                    ->color(fn (InventoryLog $record): ?string => $record->product ? null : 'danger')
                    ->icon(fn (InventoryLog $record): ?Heroicon => $record->product ? null : Heroicon::OutlinedTrash)
                    ->url(fn (InventoryLog $record): ?string => $record->product
                        ? ProductResource::getUrl('edit', ['record' => $record->product])
                        : null)
                    ->searchable(query: fn (Builder $query, string $search): Builder => self::searchByProduct($query, $search))
                    ->visible($withProductColumns),
                TextColumn::make('action')
                    ->label('Действие')
                    ->badge(),
                TextColumn::make('added_sizes')
                    ->label('Добавлены размеры')
                    ->state(fn (InventoryLog $record): array => self::getSizeNames($record->added_sizes))
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),
                TextColumn::make('removed_sizes')
                    ->label('Убраны размеры')
                    ->state(fn (InventoryLog $record): array => self::getSizeNames($record->removed_sizes))
                    ->badge()
                    ->color('danger')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Действие')
                    ->options(InventoryLogAction::class)
                    ->native(false),
                Filter::make('created_at')
                    ->schema([
                        Fieldset::make()
                            ->label('Дата')
                            ->schema([
                                DatePicker::make('created_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('created_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['created_from'] ?? null) {
                            $query->whereDate('created_at', '>=', $data['created_from']);
                        }

                        if ($data['created_until'] ?? null) {
                            $query->whereDate('created_at', '<=', $data['created_until']);
                        }
                    }),
            ])
            ->searchPlaceholder('Код товара или артикул')
            ->defaultPaginationPageOption(50)
            ->recordActions([])
            ->toolbarActions([])
            ->selectable(false);
    }

    private static function getProductTitle(InventoryLog $record): string
    {
        $product = $record->product;

        if ($product === null) {
            return 'Товар удалён';
        }

        return trim("{$product->brand?->name} {$product->sku}");
    }

    private static function getProductDescription(InventoryLog $record): string
    {
        $category = $record->product?->category?->title;

        return $category ? "{$category} · код {$record->product_id}" : "код {$record->product_id}";
    }

    /**
     * Exact match by product id or partial match by SKU.
     *
     * @param  Builder<InventoryLog>  $query
     * @return Builder<InventoryLog>
     */
    private static function searchByProduct(Builder $query, string $search): Builder
    {
        $search = trim($search);

        return $query->where(function (Builder $query) use ($search): void {
            if (ctype_digit($search)) {
                $query->where('product_id', (int)$search);
            }

            $query->orWhereIn(
                'product_id',
                Product::withTrashed()->where('sku', 'like', "%{$search}%")->select('id'),
            );
        });
    }

    /**
     * @param  array<int, int>|null  $sizeIds
     * @return list<string>
     */
    private static function getSizeNames(?array $sizeIds): array
    {
        $sizeNames = once(fn (): array => Size::query()->pluck('name', 'id')->all());

        return array_map(fn (int $sizeId): string => (string)($sizeNames[$sizeId] ?? "#{$sizeId}"), array_values($sizeIds ?? []));
    }
}
