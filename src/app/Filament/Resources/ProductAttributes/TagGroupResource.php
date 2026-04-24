<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateTagGroup;
use App\Filament\Resources\ProductAttributes\Pages\EditTagGroup;
use App\Filament\Resources\ProductAttributes\Pages\ListTagGroups;
use App\Models\Tag;
use App\Models\TagGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagGroupResource extends Resource
{
    protected static ?string $model = TagGroup::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::ProductAttributes;

    protected static ?string $modelLabel = 'Группа тегов';

    protected static ?string $pluralModelLabel = 'Группы тегов';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название группы')
                    ->required()
                    ->maxLength(255),
                Select::make('tag_ids')
                    ->label('Теги')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(function (?TagGroup $record): array {
                        return Tag::query()
                            ->where(function ($query) use ($record): void {
                                $query->whereNull('tag_group_id');
                                if ($record?->exists) {
                                    $query->orWhere('tag_group_id', $record->getKey());
                                }
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Название группы')->searchable(),
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
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultPaginationPageOption(50);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTagGroups::route('/'),
            'create' => CreateTagGroup::route('/create'),
            'edit' => EditTagGroup::route('/{record}/edit'),
        ];
    }
}
