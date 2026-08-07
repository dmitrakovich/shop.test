<?php

namespace App\Filament\Resources\Management\Audits\Pages;

use App\Filament\Resources\Management\Audits\AuditResource;
use Filament\Resources\Pages\ListRecords;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
