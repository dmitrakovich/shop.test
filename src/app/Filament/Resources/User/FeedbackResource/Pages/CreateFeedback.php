<?php

namespace App\Filament\Resources\User\FeedbackResource\Pages;

use App\Filament\Resources\User\FeedbackResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateFeedback extends CreateRecord
{
    protected static string $resource = FeedbackResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
