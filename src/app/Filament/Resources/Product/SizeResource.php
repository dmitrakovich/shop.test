<?php

namespace App\Filament\Resources\Product;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Product\SizeResource\Pages;
use App\Models\Size;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SizeResource extends Resource
{
    protected static ?string $model = Size::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::PRODUCTS;

    protected static ?string $modelLabel = 'Размеры';

    protected static ?string $pluralModelLabel = 'Размеры';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Размер')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->readOnlyOn('edit')
                    ->maxLength(36)
                    ->default('slug-'),
                Forms\Components\TextInput::make('insole')
                    ->label('Длина стельки')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Активный')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Размер')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('insole')
                    ->label('Длина стельки')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активный')
                    ->alignCenter()
                    ->boolean(),
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
            ->filters([
                Filters\Filter::make('is_active')
                    ->label('Скрыть неактивные')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->defaultPaginationPageOption(25)
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSizes::route('/'),
        ];
    }
}
