<?php

namespace App\Filament\Resources\Departures\Batches;

use App\Enums\Belpost\BelpostBatchStatus;
use App\Enums\Belpost\BelpostDirection;
use App\Enums\Belpost\BelpostPaymentType;
use App\Enums\Belpost\BelpostPostalDeliveryType;
use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Departures\Batches\Pages\CreateBatch;
use App\Filament\Resources\Departures\Batches\Pages\EditBatch;
use App\Filament\Resources\Departures\Batches\Pages\ListBatches;
use App\Filament\Resources\Departures\Batches\RelationManagers\OrdersRelationManager;
use App\Models\Orders\Batch;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Departures;

    protected static ?string $navigationLabel = 'Партии Белпочта';

    protected static ?string $modelLabel = 'Партия';

    protected static ?string $pluralModelLabel = 'Партии';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Параметры партии')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('postal_delivery_type')
                            ->label('Тип отправления')
                            ->options(BelpostPostalDeliveryType::class)
                            ->default(config('belpost.defaults.postal_delivery_type'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, callable $set): void {
                                $enum = BelpostPostalDeliveryType::tryFromFormState($state);
                                if ($enum === null) {
                                    return;
                                }
                                if ($enum->isEcommercePostal()) {
                                    $set('is_partial_receipt', false);
                                }
                                if (!$enum->supportsDeclaredValueListFlag()) {
                                    $set('is_declared_value', false);
                                }
                                if ($enum->requiresNegotiatedRateFalseForApi()) {
                                    $set('negotiated_rate', false);
                                }
                            })
                            ->native(false),
                        Select::make('direction')
                            ->label('Направление')
                            ->options(BelpostDirection::class)
                            ->default(config('belpost.defaults.direction'))
                            ->required()
                            ->native(false),
                        Select::make('payment_type')
                            ->label('Способ оплаты')
                            ->options(BelpostPaymentType::class)
                            ->default(config('belpost.defaults.payment_type'))
                            ->required()
                            ->live()
                            ->native(false),
                        TextInput::make('card_number')
                            ->label('Номер карты (л/с)')
                            ->maxLength(64)
                            ->default(config('belpost.defaults.card_number'))
                            ->visible(fn (Get $get): bool => BelpostPaymentType::tryFromFormState($get('payment_type'))?->requiresCardNumber() ?? false)
                            ->required(fn (Get $get): bool => BelpostPaymentType::tryFromFormState($get('payment_type'))?->requiresCardNumber() ?? false),
                        Toggle::make('negotiated_rate')
                            ->label('Договорной тариф')
                            ->default(config('belpost.defaults.negotiated_rate'))
                            ->visible(fn (Get $get): bool => !BelpostPostalDeliveryType::tryFromFormState($get('postal_delivery_type'))?->requiresNegotiatedRateFalseForApi()),
                        Toggle::make('is_declared_value')
                            ->label('С объявленной ценностью')
                            ->visible(fn (Get $get): bool => BelpostPostalDeliveryType::tryFromFormState($get('postal_delivery_type'))?->supportsDeclaredValueListFlag() ?? false),
                        Toggle::make('is_partial_receipt')
                            ->label('Частичная выдача вложений')
                            ->visible(fn (Get $get): bool => BelpostPostalDeliveryType::tryFromFormState($get('postal_delivery_type'))?->supportsPartialReceiptOfEnclosures() ?? true),
                    ]),
                Section::make('Белпочта')
                    ->columns(2)
                    ->schema([
                        TextInput::make('belpost_list_id')
                            ->label('ID партии в Белпочте')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('belpost_status')
                            ->label('Статус в Белпочте')
                            ->formatStateUsing(function (mixed $state): ?string {
                                if ($state instanceof BelpostBatchStatus) {
                                    return $state->getLabel();
                                }

                                if (is_string($state) && $state !== '') {
                                    return BelpostBatchStatus::tryFrom($state)?->getLabel() ?? $state;
                                }

                                return is_scalar($state) ? (string)$state : null;
                            })
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('belpost_document_id')
                            ->label('ID документа')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('dispatch_date')
                            ->label('Дата отправки')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('belpost_sync_error')
                            ->label('Последняя ошибка синхронизации')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->visible(fn (?string $state): bool => filled($state)),
                    ])
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->placeholder(fn (Batch $record): string => "Партия #{$record->id}")
                    ->searchable(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Заказов'),
                TextColumn::make('belpost_list_id')
                    ->label('Belpost ID')
                    ->placeholder('—'),
                TextColumn::make('belpost_status')
                    ->label('Статус Белпочта')
                    ->badge()
                    ->placeholder('Локальная'),
                TextColumn::make('dispatch_date')
                    ->label('Отправлена')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatches::route('/'),
            'create' => CreateBatch::route('/create'),
            'edit' => EditBatch::route('/{record}/edit'),
        ];
    }
}
