<?php

namespace App\Filament\Resources\User\Feedback\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\User\Feedback\FeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeedback extends ListRecords
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
