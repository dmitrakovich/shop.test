<?php

namespace App\Filament\Pages\Settings\Concerns;

use App\Filament\Clusters\Config\ConfigCluster;
use Filament\Support\Enums\Width;

trait ConfigClusterPage
{
    protected static ?string $cluster = ConfigCluster::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
