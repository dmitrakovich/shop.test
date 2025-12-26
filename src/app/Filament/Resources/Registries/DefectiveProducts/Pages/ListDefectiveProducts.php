<?php

namespace App\Filament\Resources\Registries\DefectiveProducts\Pages;

use App\Filament\Resources\Registries\DefectiveProducts\DefectiveProductResource;
use App\Models\DefectiveProduct;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDefectiveProducts extends ListRecords
{
    protected static string $resource = DefectiveProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все')
                ->badge(DefectiveProduct::query()->withTrashed()->count())
                ->modifyQueryUsing(function (Builder $query) {
                    /** @var Builder<DefectiveProduct> $query */
                    return $query->withTrashed();
                }),
            'active' => Tab::make('Активные')
                ->badge(DefectiveProduct::query()->count()),
            'sold' => Tab::make('Проданные')
                ->badge(DefectiveProduct::query()->onlyTrashed()->count())
                ->modifyQueryUsing(function (Builder $query) {
                    /** @var Builder<DefectiveProduct> $query */
                    return $query->onlyTrashed();
                }),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
