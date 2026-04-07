<?php

namespace App\Filament\Resources\Ads\Banners\Tables;

use App\Enums\Ads\BannerMediaCollection;
use App\Models\Ads\Banner;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Позиция')
                    ->badge(),
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Баннер')
                    ->collection(fn (Banner $record) => $record->type->isVideo()
                        ? BannerMediaCollection::DESKTOP_VIDEO_PREVIEW->value
                        : BannerMediaCollection::DESKTOP_IMAGE->value
                    )
                    ->conversion('thumb'),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('url')
                    ->label('Ссылка')
                    ->searchable(),
                // TextColumn::make('priority')
                //     ->label('Приоритет')
                //     ->numeric()
                //     ->sortable(),
                ToggleColumn::make('active')
                    ->label('Активен'),
                TextColumn::make('start_datetime')
                    ->label('Дата начала')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_datetime')
                    ->label('Дата окончания')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Удален')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // IconColumn::make('show_timer') // нужна проверка что заполнена дата окончания
                //     ->label('Показывать таймер')
                //     ->boolean(),
            ])
            ->filters([
                TrashedFilter::make()->native(false),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
                DeleteAction::make()->hiddenLabel(),
            ]);
    }
}
