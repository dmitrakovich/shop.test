<?php

namespace App\Filament\Resources\User\FeedbackResource\Pages;

use App\Filament\Resources\User\FeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeedback extends EditRecord
{
    protected static string $resource = FeedbackResource::class;

    protected ?string $maxContentWidth = '5xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
