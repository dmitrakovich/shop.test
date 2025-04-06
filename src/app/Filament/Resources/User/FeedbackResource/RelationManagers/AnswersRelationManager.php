<?php

namespace App\Filament\Resources\User\FeedbackResource\RelationManagers;

use App\Contracts\AuthorInterface;
use App\Models\Admin\AdminUser;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        /** @var AdminUser $adminUser */
        $adminUser = Auth::user();

        return $form
            ->schema([
                Forms\Components\Textarea::make('text')
                    ->label('Текст')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('publish')
                    ->label('Публиковать')
                    ->default(true),
                Forms\Components\Hidden::make('user_type')
                    ->default($adminUser::class),
                Forms\Components\Hidden::make('user_id')
                    ->default($adminUser->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_type')
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
                Tables\Columns\TextColumn::make('user')
                    ->formatStateUsing(fn (AuthorInterface $state) => $state->getFullName())
                    ->label('Пользователь')
                    ->wrap(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Текст')
                    ->limit(500)
                    ->wrap(),
                Tables\Columns\ToggleColumn::make('publish')
                    ->label('Публиковать')
                    ->alignCenter(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
