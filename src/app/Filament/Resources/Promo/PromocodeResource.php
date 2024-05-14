<?php

namespace App\Filament\Resources\Promo;

use App\Filament\Resources\Promo\PromocodeResource\Pages;
use App\Models\Promo\Promocode;
use Carbon\CarbonInterval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromocodeResource extends Resource
{
    protected static ?string $model = Promocode::class;

    protected static ?string $navigationGroup = 'promo';

    protected static ?string $modelLabel = 'Промокод';

    protected static ?string $pluralModelLabel = 'Промокоды';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Код для активации')
                    ->alphaDash()
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('sale_id')
                    ->relationship('sale', 'title')
                    ->label('Акция')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('timer_sec')
                    ->label('Время действия после активации (секунд)')
                    ->placeholder('Если не заполнено, время будет взято с акции')
                    ->datalist([60, 300, 600, 1800, 3600, 86400])
                    ->numeric(),
                Forms\Components\TextInput::make('activations_count')
                    ->label('Количество активаций')
                    ->placeholder('Оставить пустым для бесконечного количества активаций')
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Код для активации')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sale.title')
                    ->label('Связанная акция')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->wrap(),
                Tables\Columns\TextColumn::make('timer_sec')
                    ->label('Время действия')
                    ->formatStateUsing(
                        fn ($state) => CarbonInterval::seconds($state)->cascade()->forHumans(short: true)
                    ),
                Tables\Columns\TextColumn::make('activations_count')
                    ->label('Количество активаций')
                    ->getStateUsing(function (Promocode $promocode) {
                        return is_null($promocode->activations_count) ? '∞' : $promocode->activations_count;
                    }),
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
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePromocodes::route('/'),
        ];
    }
}
