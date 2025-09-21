<?php

namespace App\Filament\Resources\User\FeedbackResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Contracts\AuthorInterface;
use App\Models\Admin\AdminUser;
use App\Models\User\User;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'Ответы';

    protected static ?string $modelLabel = 'ответ';

    protected static ?string $pluralModelLabel = 'ответов';

    public function form(Schema $schema): Schema
    {
        /** @var AdminUser $adminUser */
        $adminUser = Auth::user();

        return $schema
            ->components([
                Textarea::make('text')
                    ->label('Текст')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('publish')
                    ->label('Публиковать')
                    ->default(true),
                Hidden::make('user_type')
                    ->default($adminUser::class),
                Hidden::make('user_id')
                    ->default($adminUser->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_type')
                    ->formatStateUsing(
                        /** @param  class-string<AuthorInterface>  $state */
                        fn (string $state): string => $state::getTypeName()
                    )
                    ->color(
                        /** @param  class-string<AuthorInterface>  $state */
                        fn (string $state): string => $state === User::class ? 'success' : 'primary'
                    )
                    ->label('Тип пользователя')
                    ->badge(),
                TextColumn::make('user')
                    ->formatStateUsing(fn (AuthorInterface $state) => $state->getFullName())
                    ->label('Пользователь')
                    ->wrap(),
                TextColumn::make('text')
                    ->label('Текст')
                    ->limit(500)
                    ->wrap(),
                ToggleColumn::make('publish')
                    ->label('Публиковать')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
