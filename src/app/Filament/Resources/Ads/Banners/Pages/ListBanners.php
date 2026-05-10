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
            ...$this->tabsForBannerPositions(),
        ];
    }

    /**
     * Tab id (URL/query) may differ from {@see BannerPosition::$value} where Filament needs a shorter key.
     *
     * @return array<string, Tab>
     */
    private function tabsForBannerPositions(): array
    {
        $definition = [
            ['index_main', BannerPosition::INDEX_MAIN],
            ['index_double', BannerPosition::INDEX_DOUBLE],
            ['index_category', BannerPosition::INDEX_CATEGORY],
            ['catalog', BannerPosition::CATALOG_MAIN],
            ['feedbacks', BannerPosition::FEEDBACK_MAIN],
        ];

        $tabs = [];
        foreach ($definition as [$tabId, $position]) {
            $tabs[$tabId] = Tab::make($tabId)
                ->label($position->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('position', $position));
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
