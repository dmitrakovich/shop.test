<?php

namespace App\Admin\Controllers\OrdersDistribution;

use App\Admin\Controllers\OrdersDistribution\Form\SettingsForm;
use App\Admin\Controllers\OrdersDistribution\Form\WorkScheduleForm;
use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Tab;

class SettingsController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Настройки')
            ->body(Tab::forms([
                'main' => SettingsForm::class,
                'work_schedule' => WorkScheduleForm::class,
            ]));
    }
}
