<?php

namespace App\Filament\Resources\Orders\OrderItems\Tables;

use App\Enums\Order\OrderItemStatus;
use App\Filament\Resources\Products\Products\ProductResource;
use App\Helpers\PhoneHelper;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderItemsTable
{
    /**
     * Relations the table and its export render.
     *
     * @var list<string>
     */
    public const array EAGER_LOAD = ['order:id,created_at', 'product.media', 'product.brand', 'size', 'statusLog.stock'];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order.created_at')
                    ->label('Дата заказа')
                    ->dateTime('d.m.y H:i'),
                ImageColumn::make('product_photo')
                    ->label('Фото')
                    ->state(fn (OrderItem $record): ?string => $record->product->exists
                        ? ($record->product->getFirstMediaUrl('default', 'thumb') ?: null)
                        : null)
                    ->imageHeight(60),
                TextColumn::make('product_id')
                    ->label('Товар')
                    ->formatStateUsing(fn (OrderItem $record): string => $record->product->exists
                        ? trim("{$record->product->brand?->name} {$record->product->sku}")
                        : 'Товар удалён')
                    ->description(fn (OrderItem $record): string => "код {$record->product_id}")
                    ->color(fn (OrderItem $record): ?string => $record->product->exists ? null : 'danger')
                    ->icon(fn (OrderItem $record): ?Heroicon => $record->product->exists ? null : Heroicon::OutlinedTrash)
                    ->url(fn (OrderItem $record): ?string => $record->product->exists
                        ? ProductResource::getUrl('edit', ['record' => $record->product])
                        : null)
                    ->searchable(query: fn (Builder $query, string $search): Builder => self::searchByProduct($query, $search)),
                TextColumn::make('size.name')
                    ->label('Размер')
                    ->placeholder('—'),
                TextColumn::make('order_id')
                    ->label('Заказ')
                    ->url(fn (OrderItem $record): string => url("old-admin/orders/{$record->order_id}/edit"), shouldOpenInNewTab: true),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('statusLog.stock.internal_name')
                    ->label('Склад')
                    ->placeholder('—'),
                TextColumn::make('statusLog.sended_at')
                    ->label('Отправлен')
                    ->dateTime('d.m.y H:i')
                    ->placeholder('—'),
                TextColumn::make('statusLog.completed_at')
                    ->label('Выкуплен')
                    ->dateTime('d.m.y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->multiple()
                    ->options(OrderItemStatus::class)
                    ->native(false),
                SelectFilter::make('stock')
                    ->label('Склад')
                    ->multiple()
                    ->options(fn (): array => Stock::query()
                        ->whereIn('id', OrderItemStatusLog::query()->distinct()->select('stock_id'))
                        ->orderBy('internal_name')
                        ->pluck('internal_name', 'id')
                        ->all())
                    ->query(function (Builder $query, array $data): void {
                        if (filled($data['values'] ?? null)) {
                            $query->whereHas('statusLog', fn (Builder $query) => $query->whereIn('stock_id', $data['values']));
                        }
                    })
                    ->native(false),
                Filter::make('order')
                    ->schema([
                        TextInput::make('order_id')
                            ->label('Номер заказа')
                            ->integer(),
                        TextInput::make('phone')
                            ->label('Телефон клиента')
                            ->tel(),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if (filled($data['order_id'] ?? null)) {
                            $query->where('order_id', (int)$data['order_id']);
                        }

                        if (filled($data['phone'] ?? null)) {
                            $query->whereIn(
                                'order_id',
                                PhoneHelper::constrainSearch(Order::query()->select('id'), (string)$data['phone']),
                            );
                        }
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['order_id'] ?? null)) {
                            $indicators[] = "Заказ {$data['order_id']}";
                        }

                        if (filled($data['phone'] ?? null)) {
                            $indicators[] = "Телефон {$data['phone']}";
                        }

                        return $indicators;
                    }),
                self::dateRangeFilter('created_at', 'Дата заказа', fn (Builder $query, string $operator, string $date) => $query
                    ->whereDate('created_at', $operator, $date)),
                self::dateRangeFilter('sended_at', 'Дата отправки', fn (Builder $query, string $operator, string $date) => $query
                    ->whereHas('statusLog', fn (Builder $query) => $query->whereDate('sended_at', $operator, $date))),
                self::dateRangeFilter('completed_at', 'Дата выкупа', fn (Builder $query, string $operator, string $date) => $query
                    ->whereHas('statusLog', fn (Builder $query) => $query->whereDate('completed_at', $operator, $date))),
            ])
            ->searchPlaceholder('Код товара или артикул')
            ->defaultPaginationPageOption(50)
            ->recordActions([])
            ->toolbarActions([])
            ->selectable(false);
    }

    /**
     * @param  Closure(Builder<OrderItem>, string, string): mixed  $constrain  receives the query, operator and date
     */
    private static function dateRangeFilter(string $name, string $label, Closure $constrain): Filter
    {
        return Filter::make($name)
            ->schema([
                Fieldset::make()
                    ->label($label)
                    ->schema([
                        DatePicker::make('from')
                            ->label('с:')
                            ->native(false)
                            ->closeOnDateSelection(),
                        DatePicker::make('until')
                            ->label('по:')
                            ->native(false)
                            ->closeOnDateSelection(),
                    ]),
            ])
            ->query(function (Builder $query, array $data) use ($constrain): void {
                if ($data['from'] ?? null) {
                    $constrain($query, '>=', (string)$data['from']);
                }

                if ($data['until'] ?? null) {
                    $constrain($query, '<=', (string)$data['until']);
                }
            })
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = "{$label} с " . Carbon::parse($data['from'])->format('d.m.Y');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = "{$label} по " . Carbon::parse($data['until'])->format('d.m.Y');
                }

                return $indicators;
            });
    }

    /**
     * Exact match by product id or partial match by SKU.
     *
     * @param  Builder<OrderItem>  $query
     * @return Builder<OrderItem>
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
}
