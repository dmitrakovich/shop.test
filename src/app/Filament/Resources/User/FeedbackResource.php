<?php

namespace App\Filament\Resources\User;

use App\Enums\Feedback\FeedbackType;
use App\Filament\Resources\User\FeedbackResource\Pages;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('user_name')
                                    ->label('Имя')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('user_city')
                                    ->label('Город')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('text')
                                    ->label('Текст')
                                    ->rows(4)
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Фото')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('photos')
                                    ->image()
                                    ->collection('photos')
                                    ->multiple()
                                    ->maxFiles(10)
                                    ->reorderable()
                                    ->downloadable()
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                        Forms\Components\Section::make('Видео')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('video')
                                    ->collection('video')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->downloadable()
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Rating::make('rating')
                                    ->label('Оценка')
                                    ->required()
                                    ->default(5),
                                Forms\Components\Select::make('type')
                                    ->options(FeedbackType::class)
                                    ->label('Тип')
                                    ->required()
                                    ->default(FeedbackType::REVIEW),
                                Forms\Components\Toggle::make('publish')
                                    ->label('Публиковать')
                                    ->default(true),
                            ]),
                        Forms\Components\Section::make('Связи')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Пользователь')
                                    ->relationship('user')
                                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->getFullName())
                                    ->searchable(['first_name', 'last_name', 'patronymic_name']),
                                Forms\Components\Select::make('product_id')
                                    ->label('Товар')
                                    ->relationship('product')
                                    ->getOptionLabelFromRecordUsing(fn (Product $record) => $record->nameForAdmin())
                                    ->searchable(['id', 'sku']),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Forms\Components\Hidden::make('captcha_score')
                    ->default(10),
                Forms\Components\Hidden::make('ip')
                    ->default(request()->ip()),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Имя')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_city')
                    ->label('Город')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Текст')
                    ->searchable()
                    ->wrap(),
                RatingColumn::make('rating')
                    ->label('Оценка')
                    ->sortable()
                    ->size('sm'),
                SpatieMediaLibraryImageColumn::make('photos')
                    ->collection('photos')
                    ->conversion('thumb')
                    ->label('Фото'),
                Tables\Columns\TextColumn::make('product')
                    ->formatStateUsing(fn (Product $state) => $state->nameForAdmin())
                    ->label('Товар')
                    ->wrap(),
                Tables\Columns\TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label('Кол-во ответов')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
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
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['product.brand', 'product.category']);
            })
            ->defaultSort('id', 'desc')
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
