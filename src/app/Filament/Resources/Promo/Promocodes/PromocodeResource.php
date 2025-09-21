<?php

namespace App\Filament\Resources\Promo\Promocodes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Promo\Promocodes\Pages\ManagePromocodes;
use App\Models\Promo\Promocode;
use Carbon\CarbonInterval;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromocodeResource extends Resource
{
    protected static ?string $model = Promocode::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::PROMO;

    protected static ?string $modelLabel = 'Промокод';

    protected static ?string $pluralModelLabel = 'Промокоды';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Код для активации')
                    ->alphaDash()
                    ->required()
                    ->maxLength(20),
                Select::make('sale_id')
                    ->relationship('sale', 'title')
                    ->label('Акция')
                    ->required()
                    ->native(false),
                TextInput::make('timer_sec')
                    ->label('Время действия после активации (секунд)')
                    ->placeholder('Если не заполнено, время будет взято с акции')
                    ->datalist([60, 300, 600, 1800, 3600, 86400])
                    ->numeric(),
                TextInput::make('activations_count')
                    ->label('Количество активаций')
                    ->placeholder('Оставить пустым для бесконечного количества активаций')
                    ->numeric(),
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Код для активации')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('sale.title')
                    ->label('Связанная акция')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Описание')
                    ->wrap(),
                TextColumn::make('timer_sec')
                    ->label('Время действия')
                    ->formatStateUsing(
                        fn ($state) => CarbonInterval::seconds($state)->cascade()->forHumans(short: true)
                    ),
                TextColumn::make('activations_count')
                    ->label('Количество активаций')
                    ->getStateUsing(function (Promocode $promocode) {
                        return is_null($promocode->activations_count) ? '∞' : $promocode->activations_count;
                    }),
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
            ->recordActions([
                EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePromocodes::route('/'),
        ];
    }
}
