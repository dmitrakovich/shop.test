<?php

namespace App\Filament\Resources\Products\Products\Pages;

use App\Events\Products\ProductCreated;
use App\Filament\Actions\Product\PromtAction;
use App\Filament\Resources\Products\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PromtAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['label_id']?->isNotPublished()) {
            $data['deleted_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Product $product */
        $product = $this->getRecord();

        event(new ProductCreated($product));
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('createAndRedirect')
                ->label('Создать')
                ->action('createAndRedirect')
                ->keyBindings(['mod+s']),
            Action::make('createAndContinue')
                ->label('Создать и Продолжить')
                ->action('create')
                ->color('gray'),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    public function createAndRedirect(): void
    {
        $this->create();
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
