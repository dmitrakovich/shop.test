<?php

namespace App\Filament\Actions\Product;

use App\Jobs\Elasticsearch\UpsertCatalogProductJob;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ReindexCatalogAction extends Action
{
    public static function make(?string $name = 'reindexCatalog'): static
    {
        return parent::make($name)
            ->label('Переиндексировать в каталоге')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->action(function (Product $record): void {
                UpsertCatalogProductJob::dispatch($record->id);

                Notification::make()
                    ->title('Товар поставлен в очередь на переиндексацию')
                    ->success()
                    ->send();
            });
    }
}
