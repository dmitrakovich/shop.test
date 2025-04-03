<?php

namespace App\Filament\Resources\User\FeedbackResource\Pages;

use App\Filament\Resources\User\FeedbackResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedback extends CreateRecord
{
    protected static string $resource = FeedbackResource::class;

    protected ?string $maxContentWidth = '7xl';
}
