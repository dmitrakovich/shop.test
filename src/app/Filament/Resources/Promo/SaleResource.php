<?php

namespace App\Filament\Resources\Promo;

use App\Enums\Promo\SaleAlgorithm;
use App\Filament\Resources\Promo\SaleResource\Pages;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Promo\Sale;
use App\Models\Season;
use App\Models\Style;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationGroup = 'promo';

    protected static ?string $modelLabel = 'Акция';

    protected static ?string $pluralModelLabel = 'Акции';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('label_text')
                    ->label('Текст на шильде')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('start_datetime')
                    ->label('Дата начала')
                    ->default(now())
                    ->native(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('end_datetime')
                    ->label('Дата завершения')
                    ->default(now()->endOfDay())
                    ->native(false)
                    ->required(),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('algorithm')
                        ->label('Алгоритм')
                        ->options(SaleAlgorithm::class)
                        ->default(SaleAlgorithm::SIMPLE)
                        ->native(false)
                        ->required(),
                    Forms\Components\TextInput::make('sale_percentage')
                        ->label('Скидка в процентах')
                        ->numeric()
                        ->formatStateUsing(fn ($state) => $state ? round($state * 100, 4) : null)
                        ->mutateDehydratedStateUsing(fn ($state) => $state ? round($state / 100, 4) : null)
                        ->suffix('%')
                        ->minValue(0.01)
                        ->maxValue(100)
                        ->prohibits('sale_fix')
                        ->requiredWithout('sale_fix'),
                    Forms\Components\TextInput::make('sale_fix')
                        ->label('Фиксированный скидка')
                        ->placeholder('В копейках')
                        ->numeric()
                        ->suffix('BYN')
                        ->prohibits('sale_percentage')
                        ->requiredWithout('sale_percentage'),
                ]),
                Forms\Components\Fieldset::make()
                    ->label('Фильтры (Оставить пустым, чтобы не применялся)')
                    ->schema([
                        Forms\Components\CheckboxList::make('categories')
                            ->label('Категории')
                            ->options(Category::query()->whereNotIn('id', [1, 2, 25])->pluck('title', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->columnSpanFull(),
                        Forms\Components\CheckboxList::make('collections')
                            ->label('Коллекции')
                            ->options(Collection::query()->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->columnSpanFull(),
                        Forms\Components\CheckboxList::make('styles')
                            ->label('Стиль')
                            ->options(Style::query()->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->columnSpanFull(),
                        Forms\Components\CheckboxList::make('seasons')
                            ->label('Сезон')
                            ->options(Season::query()->pluck('name', 'id'))
                            ->bulkToggleable()
                            ->columns(5)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Toggle::make('only_new')
                    ->label('Участвуют только новинки')
                    ->declined(fn (Forms\Get $get) => $get('only_discount'))
                    ->validationMessages([
                        'declined' => 'Не может быть одновременно выбрано с "Участвуют только скидки"',
                    ]),
                Forms\Components\Toggle::make('only_discount')
                    ->label('Участвуют только скидки')
                    ->declined(fn (Forms\Get $get) => $get('only_new'))
                    ->validationMessages([
                        'declined' => 'Не может быть одновременно выбрано с "Участвуют только новинки"',
                    ]),
                Forms\Components\Toggle::make('add_client_sale')
                    ->label('Клиентская скидка суммируется'),
                Forms\Components\Toggle::make('add_review_sale')
                    ->label('Суммируется со скидкой за отзывы'),
                Forms\Components\Toggle::make('has_installment')
                    ->label('Действует рассрочка'),
                Forms\Components\Toggle::make('has_fitting')
                    ->label('Действует примерка'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label_text')
                    ->label('Текст на шильде')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_datetime')
                    ->label('Дата начала')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_datetime')
                    ->label('Дата завершения')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('algorithm')
                    ->label('Алгоритм'),
                Tables\Columns\TextColumn::make('sale_percentage')
                    ->label('Скидка в %')
                    ->formatStateUsing(fn ($state) => round($state * 100, 2) . '%'),
                Tables\Columns\TextColumn::make('sale_fix')
                    ->label('Фиксированный скидка')
                    ->money('BYN', 100, 'ru'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
