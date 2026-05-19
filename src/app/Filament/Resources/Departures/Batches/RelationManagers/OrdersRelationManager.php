<?php

namespace App\Filament\Resources\Departures\Batches\RelationManagers;

use App\Enums\Order\OrderStatus;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Libraries\Belpost\Facades\ApiBelpostFacade;
use App\Models\Orders\Batch;
use App\Models\Orders\Order;
use App\Services\Belpost\BatchMailing\BelpostBatchDocumentService;
use App\Services\Belpost\BatchMailing\BelpostBatchItemService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы в партии';

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'заказы';

    public function getOwnerRecord(): Batch
    {
        $record = parent::getOwnerRecord();
        assert($record instanceof Batch);

        return $record;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№ заказа')
                    ->sortable(),
                TextColumn::make('user_full_name')
                    ->label('ФИО')
                    ->wrap(),
                TextColumn::make('city')
                    ->label('Город'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('belpost_item_id')
                    ->label('Belpost item')
                    ->placeholder('—'),
                TextColumn::make('belpost_s10code')
                    ->label('Трек S10')
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('weight')
                    ->label('Вес, г'),
            ])
            ->headerActions([
                Action::make('attachOrders')
                    ->label('Добавить заказы')
                    ->icon(Heroicon::OutlinedPlus)
                    ->visible(fn (): bool => $this->getBatch()->isBelpostEditable())
                    ->form([
                        Select::make('order_ids')
                            ->label('Заказы')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(fn (): array => Order::query()
                                ->whereNull('batch_id')
                                ->whereIn('status', OrderStatus::shipmentPreparationStatuses())
                                ->orderByDesc('id')
                                ->limit(200)
                                ->get(['id', 'first_name', 'last_name', 'patronymic_name', 'city'])
                                ->mapWithKeys(fn (Order $order): array => [
                                    $order->id => '#' . $order->id . ' — ' . trim($order->user_full_name) . ($order->city ? " ({$order->city})" : ''),
                                ])
                                ->all()),
                    ])
                    ->action(function (array $data): void {
                        $batch = $this->getBatch();

                        Order::query()
                            ->whereIn('id', $data['order_ids'])
                            ->whereNull('batch_id')
                            ->update(['batch_id' => $batch->id]);

                        Notification::make()
                            ->title('Заказы добавлены в партию')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('pushToBelpost')
                    ->label('Отправить в Белпочту')
                    ->icon(Heroicon::OutlinedCloudArrowUp)
                    ->visible(fn (Order $record): bool => $this->canSyncOrder($record) && !$record->belpost_item_id)
                    ->action(fn (Order $record) => $this->syncOrder($record, create: true)),
                Action::make('updateInBelpost')
                    ->label('Обновить')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->visible(fn (Order $record): bool => $this->canSyncOrder($record) && (bool)$record->belpost_item_id)
                    ->action(fn (Order $record) => $this->syncOrder($record, create: false)),
                Action::make('generateItemBlank')
                    ->label('Бланк')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->visible(fn (Order $record): bool => $this->getBatch()->isLinkedToBelpost() && (bool)$record->belpost_item_id)
                    ->action(function (Order $record): void {
                        try {
                            app(BelpostBatchDocumentService::class)->generateItemBlanks($this->getBatch(), $record);
                            Notification::make()->title('Бланк отправления сгенерирован')->success()->send();
                        } catch (BelpostApiException $exception) {
                            Notification::make()->title('Ошибка')->body($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('removeFromBelpost')
                    ->label('Удалить в Белпочте')
                    ->icon(Heroicon::OutlinedCloudArrowDown)
                    ->color('danger')
                    ->visible(fn (Order $record): bool => $this->canSyncOrder($record) && (bool)$record->belpost_item_id)
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        try {
                            app(BelpostBatchItemService::class)->delete($this->getBatch(), $record);
                            Notification::make()->title('Отправление удалено в Белпочте')->success()->send();
                        } catch (BelpostApiException $exception) {
                            Notification::make()->title('Ошибка')->body($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('detachOrder')
                    ->label('Отвязать')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('gray')
                    ->visible(fn (): bool => $this->getBatch()->isBelpostEditable())
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        if ($record->belpost_item_id && $this->getBatch()->isLinkedToBelpost()) {
                            try {
                                app(BelpostBatchItemService::class)->delete($this->getBatch(), $record);
                            } catch (BelpostApiException $exception) {
                                Notification::make()
                                    ->title('Не удалось удалить в Белпочте')
                                    ->body($exception->getMessage())
                                    ->warning()
                                    ->send();
                            }
                        }

                        $record->update(['batch_id' => null]);
                        Notification::make()->title('Заказ отвязан от партии')->success()->send();
                    }),
                DeleteAction::make()
                    ->label('Удалить связь')
                    ->hidden(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('id'));
    }

    private function getBatch(): Batch
    {
        return $this->getOwnerRecord();
    }

    private function canSyncOrder(Order $record): bool
    {
        $batch = $this->getBatch();

        return $batch->isLinkedToBelpost()
            && $batch->isBelpostEditable()
            && ApiBelpostFacade::isConfigured();
    }

    private function syncOrder(Order $record, bool $create): void
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
            $items = app(BelpostBatchItemService::class);
            $batch = $this->getBatch();

            if ($create) {
                $items->create($batch, $record);
            } else {
                $items->update($batch, $record);
            }

            Notification::make()
                ->title($create ? 'Отправление создано в Белпочте' : 'Отправление обновлено в Белпочте')
                ->success()
                ->send();
        } catch (BelpostApiException $exception) {
            Notification::make()
                ->title('Ошибка API Белпочты')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
