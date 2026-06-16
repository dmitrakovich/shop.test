<?php

namespace App\Filament\Resources\Users\Sms\Tables;

use App\Enums\Sms\SmsDeliveryChannel;
use App\Enums\Sms\SmsRoute;
use App\Models\Logs\SmsLog;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('admin.name')
                    ->label('Менеджер')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('order_id')
                    ->label('Заказ')
                    ->formatStateUsing(fn (?int $state): string => $state ? "#{$state}" : '—')
                    ->description(function (SmsLog $record): ?string {
                        $order = $record->order;

                        if ($order === null) {
                            return null;
                        }

                        return trim("{$order->user_full_name}, {$order->phone}");
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('route')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('delivery_channel')
                    ->label('Канал')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label('Номер телефона')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('text')
                    ->label('Текст сообщения')
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => nl2br(e((string)$state)))
                    ->html()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->description(fn (SmsLog $record): ?string => $record->status_error)
                    ->color(fn (SmsLog $record): ?string => $record->status_error ? 'danger' : null)
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('delivered_at')
                    ->label('Доставлено')
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('read_at')
                    ->label('Прочитано')
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Дата и время отправки')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['admin', 'order']))
            ->filters([
                SelectFilter::make('route')
                    ->label('Тип отправки')
                    ->options(SmsRoute::class)
                    ->native(false),
                SelectFilter::make('delivery_channel')
                    ->label('Канал доставки')
                    ->options(SmsDeliveryChannel::class)
                    ->native(false),
                Filter::make('created_at')
                    ->schema([
                        Fieldset::make()
                            ->label('Дата отправки')
                            ->schema([
                                DatePicker::make('sent_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('sent_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['sent_from'] ?? null) {
                            $query->whereDate('created_at', '>=', $data['sent_from']);
                        }

                        if ($data['sent_until'] ?? null) {
                            $query->whereDate('created_at', '<=', $data['sent_until']);
                        }
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
