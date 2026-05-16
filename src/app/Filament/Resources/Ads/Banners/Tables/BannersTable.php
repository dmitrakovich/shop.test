<?php

namespace App\Filament\Resources\Ads\Banners\Tables;

use App\Enums\Ads\BannerMediaCollection;
use App\Filament\Resources\Ads\Banners\Pages\ListBanners;
use App\Models\Ads\Banner;
use App\Repositories\BannerRepository;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('priority')
                    ->label('№')
                    ->numeric(),
                TextColumn::make('position')
                    ->label('Позиция')
                    ->badge(),
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Баннер')
                    ->collection(
                        fn (Banner $record) => $record->desktop_type->isVideo()
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
            ])
            ->filters([
                TrashedFilter::make()->native(false),
            ])
            ->reorderable(
                'priority',
                fn (mixed $livewire): bool => $livewire instanceof ListBanners
                    && filled($livewire->activeTab)
                    && $livewire->activeTab !== 'all',
            )
            ->afterReordering(fn () => app(BannerRepository::class)->clearCache())
            ->recordActions([
                EditAction::make()->hiddenLabel(),
                DeleteAction::make()->hiddenLabel(),
            ]);
    }
}
