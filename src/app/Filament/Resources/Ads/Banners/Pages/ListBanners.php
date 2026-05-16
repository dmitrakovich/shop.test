<?php

namespace App\Filament\Resources\Ads\Banners\Pages;

use App\Enums\Ads\BannerPosition;
use App\Filament\Resources\Ads\Banners\BannerResource;
use App\Models\Ads\Banner;
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
            CreateAction::make()->url(fn (): string => BannerResource::getUrl('create', [
                'activeTab' => $this->activeTab,
            ])),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('all')->label('Все'),
            ...$this->tabsForBannerPositions(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    private function tabsForBannerPositions(): array
    {
        $tabs = [];
        foreach (BannerPosition::cases() as $position) {
            $tabs[$position->value] = Tab::make($position->value)
                ->label($position->getLabel())
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => Banner::query()
                        ->setQuery($query->getQuery())
                        ->where('position', $position)
                        ->orderByPriority()
                );
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
