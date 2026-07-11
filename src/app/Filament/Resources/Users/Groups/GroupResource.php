<?php

namespace App\Filament\Resources\Users\Groups;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Users\Groups\Pages\CreateGroup;
use App\Filament\Resources\Users\Groups\Pages\EditGroup;
use App\Filament\Resources\Users\Groups\Pages\ListGroups;
use App\Models\User\Group;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Users;

    protected static ?string $modelLabel = 'Группа пользователей';

    protected static ?string $pluralModelLabel = 'Группы пользователей';

    protected static ?string $navigationLabel = 'Группы пользователей';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        // TODO: enable create/edit when product TZ for user groups is ready.
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('discount')
                    ->label('Скидка (%)')
                    ->numeric()
                    ->required()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Идентификатор')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Скидка')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->recordActions([
                // TODO: enable when product TZ for user groups is ready.
                // EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
            // TODO: enable create/edit when product TZ for user groups is ready.
            // 'create' => CreateGroup::route('/create'),
            // 'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
