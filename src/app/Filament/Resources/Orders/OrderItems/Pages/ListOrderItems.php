<?php

namespace App\Filament\Resources\Orders\OrderItems\Pages;

use App\Exports\OrderItemsExport;
use App\Filament\Resources\Orders\OrderItems\OrderItemResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListOrderItems extends ListRecords
{
    /**
     * Synchronous xlsx export takes about 12 seconds per 10 000 rows.
     */
    public const int MAX_EXPORT_ROWS = 10_000;

    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->tooltip('Выгрузить все найденные позиции с учётом фильтров')
                ->action(fn (): ?BinaryFileResponse => $this->export()),
        ];
    }

    private function export(): ?BinaryFileResponse
    {
        $query = $this->getFilteredSortedTableQuery();
        $count = (clone $query)->count();

        if ($count > self::MAX_EXPORT_ROWS) {
            Notification::make()
                ->title('Слишком много позиций для выгрузки')
                ->body(sprintf(
                    'Найдено %s, можно выгрузить не больше %s. Сузьте фильтры, например по дате заказа.',
                    number_format($count, thousands_separator: ' '),
                    number_format(self::MAX_EXPORT_ROWS, thousands_separator: ' '),
                ))
                ->warning()
                ->send();

            return null;
        }

        return Excel::download(new OrderItemsExport($query), 'Товары в заказах.xlsx');
    }
}
