<?php

namespace App\Filament\Resources\User\FeedbackResource\Pages;

use App\Filament\Resources\User\FeedbackResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditFeedback extends EditRecord
{
    protected static string $resource = FeedbackResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
