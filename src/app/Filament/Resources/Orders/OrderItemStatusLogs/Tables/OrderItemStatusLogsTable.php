<?php

namespace App\Filament\Resources\Orders\OrderItemStatusLogs\Tables;

use App\Enums\Order\OrderItemStatusLogStage;
use App\Filament\Resources\Products\Products\ProductResource;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderItemStatusLogsTable
{
    /**
     * Stage date columns in the order a unit passes them.
     */
    private const array STAGE_COLUMNS = [
        'confirmed_at' => 'Подтверждён',
        'collected_at' => 'Собран',
        'picked_up_at' => 'Упакован',
        'sended_at' => 'Отправлен',
        'completed_at' => 'Выкуплен',
        'returned_at' => 'Возврат',
        'canceled_at' => 'Отменён',
        'deleted_at' => 'Нет в наличии',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Добавлен в заказ')
                    ->dateTime('d.m.y H:i')
                    ->sortable(),
                TextColumn::make('product')
                    ->label('Товар')
                    ->state(fn (OrderItemStatusLog $record): string => self::getProductTitle($record))
                    ->description(fn (OrderItemStatusLog $record): ?string => self::getProductDescription($record))
                    ->color(fn (OrderItemStatusLog $record): ?string => match (true) {
                        $record->orderItem === null => 'gray',
                        !$record->orderItem->product->exists => 'danger',
                        default => null,
                    })
                    ->icon(fn (OrderItemStatusLog $record): ?Heroicon => $record->orderItem && !$record->orderItem->product->exists
                        ? Heroicon::OutlinedTrash
                        : null)
                    ->url(fn (OrderItemStatusLog $record): ?string => $record->orderItem?->product->exists
                        ? ProductResource::getUrl('edit', ['record' => $record->orderItem->product])
                        : null)
                    ->searchable(query: fn (Builder $query, string $search): Builder => self::searchByProduct($query, $search)),
                TextColumn::make('orderItem.size.name')
                    ->label('Размер')
                    ->placeholder('—'),
                TextColumn::make('orderItem.order_id')
                    ->label('Заказ')
                    ->url(fn (OrderItemStatusLog $record): ?string => $record->orderItem
                        ? url("old-admin/orders/{$record->orderItem->order_id}/edit")
                        : null, shouldOpenInNewTab: true)
                    ->placeholder('—'),
                TextColumn::make('stock.internal_name')
                    ->label('Склад')
                    ->placeholder('—'),
                TextColumn::make('stage')
                    ->label('Этап')
                    ->state(fn (OrderItemStatusLog $record): OrderItemStatusLogStage => $record->getStage())
                    ->badge(),
                ...array_map(
                    fn (string $column, string $label): TextColumn => TextColumn::make($column)
                        ->label($label)
                        ->dateTime('d.m.y H:i')
                        ->placeholder('—')
                        ->toggleable(),
                    array_keys(self::STAGE_COLUMNS),
                    self::STAGE_COLUMNS,
                ),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->label('Этап')
                    ->options(OrderItemStatusLogStage::class)
                    ->query(function (Builder $query, array $data): void {
                        $stage = OrderItemStatusLogStage::tryFrom((string)($data['value'] ?? ''));
                        $stage?->applyToQuery($query);
                    })
                    ->native(false),
                SelectFilter::make('stock_id')
                    ->label('Склад')
                    ->multiple()
                    ->options(fn (): array => Stock::query()
                        ->whereIn('id', OrderItemStatusLog::withTrashed()->distinct()->select('stock_id'))
                        ->orderBy('internal_name')
                        ->pluck('internal_name', 'id')
                        ->all())
                    ->native(false),
                Filter::make('order_id')
                    ->schema([
                        TextInput::make('order_id')
                            ->label('Номер заказа')
                            ->integer(),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if (filled($data['order_id'] ?? null)) {
                            $query->whereIn(
                                'order_item_id',
                                OrderItem::query()->where('order_id', (int)$data['order_id'])->select('id'),
                            );
                        }
                    })
                    ->indicateUsing(fn (array $data): ?string => filled($data['order_id'] ?? null)
                        ? "Заказ {$data['order_id']}"
                        : null),
                Filter::make('created_at')
                    ->schema([
                        Fieldset::make()
                            ->label('Добавлен в заказ')
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
            ->recordActions([])
            ->toolbarActions([])
            ->selectable(false);
    }

    private static function getProductTitle(OrderItemStatusLog $record): string
    {
        $orderItem = $record->orderItem;

        if ($orderItem === null) {
            return 'Позиция удалена из заказа';
        }

        if (!$orderItem->product->exists) {
            return 'Товар удалён';
        }

        return trim("{$orderItem->product->brand?->name} {$orderItem->product->sku}");
    }

    private static function getProductDescription(OrderItemStatusLog $record): ?string
    {
        return $record->orderItem ? "код {$record->orderItem->product_id}" : null;
    }

    /**
     * Exact match by product id or partial match by SKU.
     *
     * @param  Builder<OrderItemStatusLog>  $query
     * @return Builder<OrderItemStatusLog>
     */
    private static function searchByProduct(Builder $query, string $search): Builder
    {
        $search = trim($search);

        return $query->whereIn(
            'order_item_id',
            OrderItem::query()
                ->select('id')
                ->where(function (Builder $query) use ($search): void {
                    if (ctype_digit($search)) {
                        $query->where('product_id', (int)$search);
                    }

                    $query->orWhereIn(
                        'product_id',
                        Product::withTrashed()->where('sku', 'like', "%{$search}%")->select('id'),
                    );
                }),
        );
    }
}
