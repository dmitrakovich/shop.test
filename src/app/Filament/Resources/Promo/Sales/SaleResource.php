<?php

namespace App\Filament\Resources\Promo\Sales;

use App\Enums\Filament\NavGroup;
use App\Enums\Promo\SaleAlgorithm;
use App\Filament\Resources\Promo\Sales\Pages\CreateSale;
use App\Filament\Resources\Promo\Sales\Pages\EditSale;
use App\Filament\Resources\Promo\Sales\Pages\ListSales;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Promo\Sale;
use App\Models\Season;
use App\Models\Style;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Promo;

    protected static ?string $modelLabel = 'Акция';

    protected static ?string $pluralModelLabel = 'Акции';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('label_text')
                    ->label('Текст на шильде')
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('start_datetime')
                    ->label('Дата начала')
                    ->default(now())
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required(),
                DateTimePicker::make('end_datetime')
                    ->label('Дата завершения')
                    ->default(now()->endOfDay())
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required(),
                Grid::make(3)->schema([
                    Select::make('algorithm')
                        ->label('Алгоритм')
                        ->options(SaleAlgorithm::class)
                        ->default(SaleAlgorithm::SIMPLE)
                        ->native(false)
                        ->required(),
                    TextInput::make('sale_percentage')
                        ->label('Скидка в процентах')
                        ->prohibits('sale_fix')
                        ->requiredWithout('sale_fix'),
                    TextInput::make('sale_fix')
                        ->disabled()
                        ->helperText('Функционал в разработке')
                        ->label('Фиксированный скидка')
                        ->placeholder('В копейках')
                        ->numeric()
                        ->suffix('BYN')
                        ->prohibits('sale_percentage')
                        ->requiredWithout('sale_percentage'),
                ]),
                Repeater::make('promocodes')
                    ->label('Активация по промокоду')
                    ->helperText('Если не выбрано, активация не требуется. Может быть выбрано несколько промокодов.')
                    ->addActionLabel('Добавить промокод')
                    ->columnSpanFull()
                    ->columns(12)
                    ->relationship('promocodes')
                    ->schema([
                        TextInput::make('code')
                            ->label('Код для активации')
                            ->alphaDash()
                            ->columnSpan(3)
                            ->required()
                            ->maxLength(20),
                        TextInput::make('timer_sec')
                            ->label('Время действия')
                            ->columnSpan(2)
                            ->placeholder('в секундах')
                            ->helperText('* после активации')
                            ->datalist([60, 300, 600, 1800, 3600, 86400])
                            ->numeric(),
                        TextInput::make('activations_count')
                            ->label('Количество активаций')
                            ->columnSpan(2)
                            ->helperText('Оставить пустым для ∞ кол-ва активаций')
                            ->numeric(),
                        TextInput::make('description')
                            ->label('Описание')
                            ->columnSpan(5)
                            ->maxLength(255),
                    ]),
                Fieldset::make()
                    ->label('Фильтры (Оставить пустым, чтобы не применялся)')
                    ->schema([
                        CheckboxList::make('categories')
                            ->label('Категории')
                            ->options(Category::query()->whereNotIn('id', [1, 2, 25])->pluck('title', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->columnSpanFull(),
                        CheckboxList::make('collections')
                            ->label('Коллекции')
                            ->options(Collection::query()->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->columnSpanFull(),
                        CheckboxList::make('styles')
                            ->label('Стиль')
                            ->options(Style::query()->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->columnSpanFull(),
                        CheckboxList::make('seasons')
                            ->label('Сезон')
                            ->options(Season::query()->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ]),
                Toggle::make('only_new')
                    ->label('Участвуют только новинки')
                    ->declined(fn (Get $get) => $get('only_discount'))
                    ->validationMessages([
                        'declined' => 'Не может быть одновременно выбрано с "Участвуют только скидки"',
                    ]),
                Toggle::make('only_discount')
                    ->label('Участвуют только скидки')
                    ->declined(fn (Get $get) => $get('only_new'))
                    ->validationMessages([
                        'declined' => 'Не может быть одновременно выбрано с "Участвуют только новинки"',
                    ]),
                Toggle::make('add_client_sale')
                    ->label('Клиентская скидка суммируется'),
                Toggle::make('add_review_sale')
                    ->label('Суммируется со скидкой за отзывы'),

                Fieldset::make()
                    ->label('Способы оплаты')
                    ->schema([
                        Toggle::make('has_installment')
                            ->label('Возможна рассрочка'),
                        Toggle::make('has_cod')
                            ->label('Возможна оплата при получении'),
                    ]),
                Fieldset::make()
                    ->label('Способы доставки')
                    ->schema([
                        Toggle::make('has_fitting')
                            ->label('Возможна примерка'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('label_text')
                    ->label('Текст на шильде')
                    ->searchable(),
                TextColumn::make('start_datetime')
                    ->label('Дата начала')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_datetime')
                    ->label('Дата завершения')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('algorithm')
                    ->label('Алгоритм'),
                TextColumn::make('sale_percentage')
                    ->formatStateUsing(function ($state) {
                        $discounts = explode(',', $state);
                        $formattedDiscounts = array_map(
                            fn ($discount) => round(trim($discount) * 100, 2) . '%',
                            $discounts
                        );

                        return implode(', ', $formattedDiscounts);
                    })
                    ->label('Скидка в %'),
                // Tables\Columns\TextColumn::make('sale_fix')
                //     ->label('Фиксированный скидка')
                //     ->money('BYN', 100, 'ru'),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'edit' => EditSale::route('/{record}/edit'),
        ];
    }
}
