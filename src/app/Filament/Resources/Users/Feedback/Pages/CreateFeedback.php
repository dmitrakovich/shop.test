<?php

namespace App\Filament\Resources\Users\Feedback\Pages;

use App\Filament\Resources\Users\Feedback\FeedbackResource;
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
