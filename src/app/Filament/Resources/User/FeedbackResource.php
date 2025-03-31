<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationGroup = 'user';

    protected static ?string $modelLabel = 'Отзывы';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('user_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_city')
                    ->maxLength(255),
                Forms\Components\Textarea::make('text')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('captcha_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('publish')
                    ->required(),
                Forms\Components\TextInput::make('ip')
                    ->required()
                    ->maxLength(45),
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('publish')
                    ->label('Публиковать'),
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
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            // 'create' => Pages\CreateFeedback::route('/create'),
            // 'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
