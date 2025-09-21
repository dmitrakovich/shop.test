<?php

namespace App\Filament\Resources\User\UserResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlacklistRelationManager extends RelationManager
{
    protected static string $relationship = 'blacklistLogs';

    protected static ?string $title = 'Черный список';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->rows(2),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $table->modifyQueryUsing(
            fn (Builder $query) => $query
                ->withTrashed()
                ->orderBy('id', 'desc')
        );

        return $table
            ->columns([
                TextColumn::make('comment')
                    ->label('Комментарий'),
                TextColumn::make('created_at')
                    ->label('Дата добавления')
                    ->dateTime('d.m.Y H:i:s'),
                TextColumn::make('deleted_at')
                    ->label('Дата удаления')
                    ->dateTime('d.m.Y H:i:s'),
            ])->headerActions([
                CreateAction::make()
                    ->label('Добавить в черный список'),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Удалить из черного списка'),
            ]);
    }
}
