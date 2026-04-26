<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateCategory;
use App\Filament\Resources\ProductAttributes\Pages\EditCategory;
use App\Filament\Resources\ProductAttributes\Pages\ListCategories;
use App\Models\Category;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Products;

    protected static ?string $modelLabel = 'Категория';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title')
                    ->label('Название на сайте')
                    ->required()
                    ->maxLength(255),
                TextInput::make('one_c_name')
                    ->label('Название в 1С'),
                Textarea::make('description')
                    ->label('Описание')
                    ->rows(4),
                Select::make('parent_id')
                    ->label('Родительская категория')
                    ->options(fn (?Category $record): array => self::parentCategoryOptions($record))
                    ->required()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('seo')->limit(40),
                TextColumn::make('path')->label('Path'),
                TextColumn::make('title')->label('Название')->searchable(),
                TextColumn::make('description')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parentCategory.title')
                    ->label('Родитель')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultPaginationPageOption(30);
    }

    /**
     * @return array<int|string, string>
     */
    private static function parentCategoryOptions(?Category $record): array
    {
        $tree = Category::getFormattedTree();
        if (!$record) {
            return $tree;
        }

        $excludeIds = $record->descendants()->get(['id'])->pluck('id')->push($record->id)->all();

        return array_diff_key($tree, array_flip($excludeIds));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
