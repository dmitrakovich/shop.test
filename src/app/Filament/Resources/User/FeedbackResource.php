<?php

namespace App\Filament\Resources\User;

use App\Enums\Feedback\FeedbackType;
use App\Filament\Resources\User\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationGroup = 'user';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_city')
                    ->label('Город')
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_id')
                    ->disabled()
                    ->numeric(),
                Rating::make('rating')
                    ->label('Оценка')
                    ->required()
                    ->default(5),
                Forms\Components\Textarea::make('text')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'id')
                    ->label('Товар'),
                Forms\Components\Select::make('type')
                    ->options(FeedbackType::class)
                    ->required()
                    ->default(FeedbackType::REVIEW),
                Forms\Components\Toggle::make('publish')
                    ->label('Публиковать')
                    ->default(true),
                Forms\Components\Hidden::make('captcha_score')
                    ->default(10),
                Forms\Components\Hidden::make('ip')
                    ->default(request()->ip()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Имя')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_city')
                    ->label('Город'),
                Tables\Columns\TextColumn::make('text')
                    ->label('Текст')
                    ->searchable()
                    ->wrap(),
                RatingColumn::make('rating')
                    ->label('Оценка')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('product.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('publish')
                    ->label('Публиковать')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('ip')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                //
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                // Tables\Actions\EditAction::make()->hiddenLabel(),
                // Tables\Actions\DeleteAction::make()->hiddenLabel(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
