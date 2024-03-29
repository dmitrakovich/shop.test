<?php

namespace App\Admin\Controllers\Analytics;

use App\Enums\Order\UtmEnum;

/**
 * @property-read string $instance_name
 */
class OrderSourceDetailController extends OrderSourceController
{
    /**
     * Generates additional grid columns for the given grid.
     *
     * @param  $grid  The grid object to generate columns for.
     */
    protected function additionalGridColumns($grid): void
    {
        $grid->column('channel_name', 'Канал')->display(fn () => UtmEnum::tryFrom($this->instance_name)?->channelName());
        $grid->column('company_name', 'Компания')->display(fn () => UtmEnum::tryFrom($this->instance_name)?->companyName());
    }
}
