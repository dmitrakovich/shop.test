<?php

namespace App\Filament\Resources\User\UserResource\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlacklistRelationManager extends RelationManager
{
    protected static string $relationship = 'blacklistLogs';

    protected static ?string $title = 'Черный список';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата добавления')
                    ->dateTime('d.m.Y H:i:s'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Дата удаления')
                    ->dateTime('d.m.Y H:i:s'),
            ])->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить в черный список'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить из черного списка'),
            ]);
    }
}
