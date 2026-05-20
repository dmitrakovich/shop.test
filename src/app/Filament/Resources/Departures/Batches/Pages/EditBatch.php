<?php

namespace App\Filament\Resources\Departures\Batches\Pages;

use App\Enums\Belpost\BelpostPostalDeliveryType;
use App\Filament\Resources\Departures\Batches\BatchResource;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Libraries\Belpost\Facades\ApiBelpostFacade;
use App\Models\Orders\Batch;
use App\Services\Belpost\BatchMailing\BelpostBatchDocumentService;
use App\Services\Belpost\BatchMailing\BelpostBatchItemService;
use App\Services\Belpost\BatchMailing\BelpostBatchListService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditBatch extends EditRecord
{
    protected static string $resource = BatchResource::class;

    /**
     * @return array<Action | ActionGroup | DeleteAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                $this->createBelpostAction(),
                $this->updateBelpostAction(),
                $this->syncItemsAction(),
                $this->commitBelpostAction(),
                $this->generateBlanksAction(),
                $this->downloadBlanksAction(),
                $this->refreshBelpostAction(),
                $this->deleteBelpostAction(),
            ])
                ->label('Белпочта')
                ->icon(Heroicon::OutlinedTruck)
                ->color('primary')
                ->button()
                ->dropdownWidth(Width::Medium),
            DeleteAction::make()
                ->before(function (Batch $record): void {
                    if ($record->isLinkedToBelpost() && $record->isBelpostEditable()) {
                        app(BelpostBatchListService::class)->delete($record);
                    }
                }),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Партия сохранена';
    }

    public function getRecord(): Batch
    {
        $record = parent::getRecord();
        assert($record instanceof Batch);

        return $record;
    }

    /**
     * When the tariff is e-commerce, the cabinet must not persist “partial receipt”: API treats it like attachment declarations.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $enum = BelpostPostalDeliveryType::tryFromFormState($data['postal_delivery_type'] ?? null);
        if ($enum?->isEcommercePostal()) {
            $data['is_partial_receipt'] = false;
        }
        if ($enum !== null && !$enum->supportsDeclaredValueListFlag()) {
            $data['is_declared_value'] = false;
        }
        if ($enum?->requiresNegotiatedRateFalseForApi()) {
            $data['negotiated_rate'] = false;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if (!$record->isLinkedToBelpost() || !$record->isBelpostEditable()) {
            return;
        }

        try {
            app(BelpostBatchListService::class)->update($record);
            Notification::make()
                ->title('Параметры партии обновлены в Белпочте')
                ->success()
                ->send();
        } catch (BelpostApiException $exception) {
            $record->update(['belpost_sync_error' => $exception->getMessage()]);
            Notification::make()
                ->title('Не удалось обновить партию в Белпочте')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    private function createBelpostAction(): Action
    {
        return Action::make('createInBelpost')
            ->label('Создать партию в Белпочте')
            ->icon(Heroicon::OutlinedCloudArrowUp)
            ->color('primary')
            ->visible(fn (Batch $record): bool => !$record->isLinkedToBelpost())
            ->requiresConfirmation()
            ->action(function (Batch $record): void {
                $this->runBelpostAction(
                    fn () => app(BelpostBatchListService::class)->create($record),
                    'Партия создана в Белпочте',
                );
            });
    }

    private function updateBelpostAction(): Action
    {
        return Action::make('updateInBelpost')
            ->label('Обновить параметры в Белпочте')
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost() && $record->isBelpostEditable())
            ->action(function (Batch $record): void {
                $this->runBelpostAction(
                    fn () => app(BelpostBatchListService::class)->update($record),
                    'Партия обновлена в Белпочте',
                );
            });
    }

    private function syncItemsAction(): Action
    {
        return Action::make('syncBelpostItems')
            ->label('Синхронизировать заказы в Белпочте')
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost() && $record->isBelpostEditable())
            ->requiresConfirmation()
            ->modalDescription('Все заказы партии будут созданы или обновлены в Белпочте.')
            ->action(function (Batch $record): void {
                $this->runBelpostAction(function () use ($record): void {
                    app(BelpostBatchItemService::class)->syncAll($record);
                }, 'Отправления синхронизированы');
            });
    }

    private function commitBelpostAction(): Action
    {
        return Action::make('commitBelpost')
            ->label('Сформировать партию в Белпочте')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost() && $record->isBelpostEditable())
            ->requiresConfirmation()
            ->modalDescription('После формирования изменить отправления будет нельзя.')
            ->action(function (Batch $record): void {
                $this->runBelpostAction(
                    fn () => app(BelpostBatchListService::class)->commit($record),
                    'Партия сформирована в Белпочте',
                );
            });
    }

    private function generateBlanksAction(): Action
    {
        return Action::make('generateBelpostBlanks')
            ->label('Сгенерировать бланки')
            ->color('warning')
            ->icon(Heroicon::OutlinedDocumentText)
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost())
            ->disabled(fn (Batch $record): bool => !$record->canGenerateBelpostBlanks())
            ->tooltip(fn (Batch $record): ?string => $record->canGenerateBelpostBlanks()
                ? null
                : 'Сначала нажмите «Сформировать партию»')
            ->action(function (Batch $record): void {
                $this->runBelpostAction(
                    fn () => app(BelpostBatchDocumentService::class)->generateBatchBlanks($record),
                    'Генерация бланков запущена',
                );
            });
    }

    private function downloadBlanksAction(): Action
    {
        return Action::make('downloadBelpostBlanks')
            ->label('Скачать бланки')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost())
            ->disabled(fn (Batch $record): bool => !$record->canGenerateBelpostBlanks())
            ->tooltip(fn (Batch $record): ?string => $record->canGenerateBelpostBlanks()
                ? null
                : 'Сначала нажмите «Сформировать партию»')
            ->action(function (Batch $record) {
                if (!ApiBelpostFacade::isConfigured()) {
                    Notification::make()
                        ->title('API Белпочты не настроен')
                        ->body('Укажите BELPOST_API_TOKEN в .env')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $response = app(BelpostBatchDocumentService::class)->download($record);

                    return response()->streamDownload(
                        static fn () => print ($response->body()),
                        'belpost-batch-' . $record->id . '.zip',
                        ['Content-Type' => $response->header('Content-Type') ?: 'application/zip'],
                    );
                } catch (BelpostApiException $exception) {
                    $record->update(['belpost_sync_error' => $exception->getMessage()]);
                    Notification::make()
                        ->title('Не удалось скачать бланки')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private function refreshBelpostAction(): Action
    {
        return Action::make('refreshFromBelpost')
            ->label('Загрузить из Белпочты')
            ->icon(Heroicon::OutlinedCloudArrowDown)
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost())
            ->action(function (Batch $record): void {
                $this->runBelpostAction(
                    fn () => app(BelpostBatchListService::class)->fetch($record),
                    'Данные партии получены из Белпочты',
                );
            });
    }

    private function deleteBelpostAction(): Action
    {
        return Action::make('deleteFromBelpost')
            ->label('Удалить партию')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->visible(fn (Batch $record): bool => $record->isLinkedToBelpost() && $record->isBelpostEditable())
            ->requiresConfirmation()
            ->action(function (Batch $record): void {
                $this->runBelpostAction(
                    fn () => app(BelpostBatchListService::class)->delete($record),
                    'Партия удалена в Белпочте',
                );
            });
    }

    /**
     * @param  callable(): mixed  $callback
     */
    private function runBelpostAction(callable $callback, string $successTitle): void
    {
        if (!ApiBelpostFacade::isConfigured()) {
            Notification::make()
                ->title('API Белпочты не настроен')
                ->body('Укажите BELPOST_API_TOKEN в .env')
                ->danger()
                ->send();

            return;
        }

        try {
            $callback();
            $this->record->refresh();
            $this->fillForm();
            Notification::make()->title($successTitle)->success()->send();
        } catch (BelpostApiException $exception) {
            $this->getRecord()->update(['belpost_sync_error' => $exception->getMessage()]);
            Notification::make()
                ->title('Ошибка API Белпочты')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
