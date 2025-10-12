<?php

namespace App\Filament\Resources\Users\Feedback;

use App\Enums\Feedback\FeedbackType;
use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Users\Feedback\Pages\CreateFeedback;
use App\Filament\Resources\Users\Feedback\Pages\EditFeedback;
use App\Filament\Resources\Users\Feedback\Pages\ListFeedback;
use App\Filament\Resources\Users\Feedback\RelationManagers\AnswersRelationManager;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Users;

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('user_name')
                                    ->label('Имя')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('user_city')
                                    ->label('Город')
                                    ->maxLength(255),
                                Textarea::make('text')
                                    ->label('Текст')
                                    ->rows(4)
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make('Фото')
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
                        Section::make('Видео')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('video')
                                    ->acceptedFileTypes([
                                        'video/mp4',
                                        'video/avi',
                                        'video/mpeg',
                                        'video/quicktime',
                                    ])
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

                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Rating::make('rating')
                                    ->label('Оценка')
                                    ->required()
                                    ->default(5),
                                Select::make('type')
                                    ->options(FeedbackType::class)
                                    ->label('Тип')
                                    ->required()
                                    ->default(FeedbackType::REVIEW)
                                    ->disableOptionWhen(
                                        fn (int $value): bool => FeedbackType::from($value)->isDisabled()
                                    ),
                                Toggle::make('publish')
                                    ->label('Публиковать')
                                    ->default(true),
                            ]),
                        Section::make('Связи')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Пользователь')
                                    ->relationship('user')
                                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->getFullName())
                                    ->searchable(['first_name', 'last_name', 'patronymic_name']),
                                Select::make('product_id')
                                    ->label('Товар')
                                    ->relationship('product')
                                    ->getOptionLabelFromRecordUsing(fn (Product $record) => $record->nameForAdmin())
                                    ->searchable(['id', 'sku']),
                                SpatieMediaLibraryImageEntry::make('product.media')
                                    ->label('Фото товара')
                                    ->conversion('normal')
                                    ->imageSize('100%')
                                    ->limit(1),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Hidden::make('captcha_score')
                    ->default(10),
                Hidden::make('ip')
                    ->default(request()->ip()),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('user_city')
                    ->label('Город')
                    ->toggleable(),
                TextColumn::make('text')
                    ->label('Текст')
                    ->searchable()
                    ->limit(500)
                    ->wrap(),
                RatingColumn::make('rating')
                    ->label('Оценка')
                    ->sortable()
                    ->size('sm'),
                SpatieMediaLibraryImageColumn::make('photos')
                    ->collection('photos')
                    ->conversion('thumb')
                    ->label('Фото'),
                TextColumn::make('product')
                    ->formatStateUsing(fn (Product $state) => $state->nameForAdmin())
                    ->label('Товар')
                    ->wrap(),
                TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label('Кол-во ответов')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('publish')
                    ->label('Публиковать')
                    ->alignCenter(),
                TextColumn::make('ip')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedback::route('/'),
            'create' => CreateFeedback::route('/create'),
            'edit' => EditFeedback::route('/{record}/edit'),
        ];
    }
}
