<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Pages;

use App\Filament\Resources\Products\RatingAlgorithms\RatingAlgorithmResource;
use App\Models\RatingAlgorithm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRatingAlgorithm extends EditRecord
{
    protected static string $resource = RatingAlgorithmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(function (): bool {
                    /** @var RatingAlgorithm $record */
                    $record = $this->getRecord();

                    return !$record->isUsedInRatingConfig();
                }),
        ];
    }
}
