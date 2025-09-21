<?php

namespace App\Filament\Resources\Product\Sizes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Product\Sizes\Pages\ListSizes;
use App\Models\Size;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SizeResource extends Resource
{
    protected static ?string $model = Size::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::PRODUCTS;

    protected static ?string $modelLabel = 'Размеры';

    protected static ?string $pluralModelLabel = 'Размеры';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Размер')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->readOnlyOn('edit')
                    ->maxLength(36)
                    ->default('slug-'),
                TextInput::make('insole')
                    ->label('Длина стельки')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Активный')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Размер')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('insole')
                    ->label('Длина стельки')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Активный')
                    ->alignCenter()
                    ->boolean(),
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
            ->filters([
                Filter::make('is_active')
                    ->label('Скрыть неактивные')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSizes::route('/'),
        ];
    }
}
