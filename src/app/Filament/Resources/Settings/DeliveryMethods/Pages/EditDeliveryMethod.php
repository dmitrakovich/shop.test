<?php

namespace App\Filament\Resources\Settings\DeliveryMethods\Pages;

use App\Filament\Resources\Settings\DeliveryMethods\DeliveryMethodResource;
use Deliveries\DeliveryMethod;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryMethod extends EditRecord
{
    protected static string $resource = DeliveryMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var DeliveryMethod $record */
        $record = $this->getRecord();
        $data['instance'] = $record->getRawOriginal('instance');

        return $data;
    }
}
