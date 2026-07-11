<?php

namespace App\Filament\Resources\Settings\DeliveryMethods;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Settings\DeliveryMethods\Pages\CreateDeliveryMethod;
use App\Filament\Resources\Settings\DeliveryMethods\Pages\EditDeliveryMethod;
use App\Filament\Resources\Settings\DeliveryMethods\Pages\ListDeliveryMethods;
use Deliveries\DeliveryMethod;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DeliveryMethodResource extends Resource
{
    protected static ?string $model = DeliveryMethod::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $modelLabel = 'Способ доставки';

    protected static ?string $pluralModelLabel = 'Способы доставки';

    protected static ?string $navigationLabel = 'Способы доставки';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'settings/delivery-methods';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('instance')
                    ->label('Класс')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->dehydrated(),
                Toggle::make('active')
                    ->label('Активен')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название способа доставки')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('instance')
                    ->label('Класс')
                    ->getStateUsing(fn (DeliveryMethod $record): string => (string)$record->getRawOriginal('instance'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('active')
                    ->label('Активен'),
            ])
            ->defaultSort('id')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryMethods::route('/'),
            'create' => CreateDeliveryMethod::route('/create'),
            'edit' => EditDeliveryMethod::route('/{record}/edit'),
        ];
    }
}
