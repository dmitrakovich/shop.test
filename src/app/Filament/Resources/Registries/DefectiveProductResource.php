<?php

namespace App\Filament\Resources\Registries;

use App\Filament\Resources\Registries\DefectiveProductResource\Pages;
use App\Models\DefectiveProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DefectiveProductResource extends Resource
{
    protected static ?string $model = DefectiveProduct::class;

    protected static ?string $navigationGroup = 'registries';

    protected static ?string $modelLabel = 'Реестр брака';

    protected static ?string $pluralModelLabel = 'Реестр брака';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
                Forms\Components\Select::make('size_id')
                    ->relationship('size', 'name')
                    ->required(),
                Forms\Components\TextInput::make('reason')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Бракованные товары отсутствуют')
            ->columns([
                Tables\Columns\TextColumn::make('product.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListDefectiveProducts::route('/'),
            'create' => Pages\CreateDefectiveProduct::route('/create'),
            'edit' => Pages\EditDefectiveProduct::route('/{record}/edit'),
        ];
    }
}
