<?php

namespace App\Filament\Resources\Ads\Banners\Pages;

use App\Enums\Ads\BannerPosition;
use App\Filament\Resources\Ads\Banners\BannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('all')
                ->label('Все'),
            'main' => Tab::make('main')
                ->label('На главной')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('position', [
                    BannerPosition::INDEX_MAIN,
                    BannerPosition::INDEX_DOUBLE,
                    BannerPosition::INDEX_CATEGORY,
                ])),
            'catalog' => Tab::make('catalog')
                ->label('В каталоге')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('position', BannerPosition::CATALOG_MAIN)),
            'menu' => Tab::make('menu')
                ->label('В меню')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('position', [
                    BannerPosition::MAIN_MENU_CATALOG,
                ]))
                ->extraAttributes(['class' => 'pointer-events-none']),
            'feedbacks' => Tab::make('feedbacks')
                ->label('В отзывах')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('position', BannerPosition::FEEDBACK_MAIN)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'main';
    }
}
