<?php

namespace App\Filament\Resources\Users\Groups;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Users\Groups\Pages\ListGroups;
use App\Models\User\Group;
use Filament\Resources\Resource;
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
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
        ];
    }
}
