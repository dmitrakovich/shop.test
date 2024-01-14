<?php

namespace App\Admin\Controllers\OrdersDistribution;

use App\Admin\Controllers\OrdersDistribution\Form\LogGrid;
use App\Admin\Controllers\OrdersDistribution\Form\SettingsForm;
use App\Admin\Controllers\OrdersDistribution\Form\WorkScheduleForm;
use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Tab;

class SettingsController extends Controller
{
    public function index(Content $content)
    {
        $activeTab = request()->input('active');
        $logGrid = app()->make(LogGrid::class);
        $tabs = Tab::forms([
            'main' => SettingsForm::class,
            'work_schedule' => WorkScheduleForm::class,
        ]);
        if ($activeTab == 'log') {
            $tabs->add($logGrid->title, $logGrid->index(), true, 'log');
        } else {
            $tabs->addLink($logGrid->title, 'settings?active=log');
        }

        return $content
            ->title('Настройки')
            ->body($tabs);
    }
}
