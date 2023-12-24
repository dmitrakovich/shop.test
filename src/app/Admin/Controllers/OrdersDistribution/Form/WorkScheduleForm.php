<?php

namespace App\Admin\Controllers\OrdersDistribution\Form;

use App\Models\Config;
use App\Models\WorkSchedule;
use App\Services\AdministratorService;

use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkScheduleForm extends Form
{
    public $title = 'График работы';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $scheduleData = $request->all() ?? [];
        foreach ($scheduleData['schedule'] as $date => $adminIds) {
            $adminIdsArr = array_keys(array_filter($adminIds, function ($value) {
                return $value === 'true';
            }));
            WorkSchedule::whereNotIn('admin_user_id', $adminIdsArr)->where('date', $date)->delete();
            foreach ($adminIdsArr as $adminId) {
                WorkSchedule::updateOrCreate([
                    'admin_user_id' => $adminId,
                    'date' => $date
                ]);
            }
        }
        admin_success('Настройки успешно сохранены!');
        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $distribOrderSetup = Config::findCacheable('distrib_order_setup');
        $admins = app(AdministratorService::class)->getAdministratorList();
        $date = request()->input('date') ?? date('Y-m');
        $workSchedules = WorkSchedule::select('admin_user_id', 'date')->whereBetween(
            'date',
            [
                Carbon::parse($date)->startOfMonth(),
                Carbon::parse($date)->endOfMonth()
            ]
        )->get();
        $this->html(view('admin.orders_distribution.work_schedule', [
            'admins' => $admins,
            'date' => $date,
            'schedule' => $distribOrderSetup['schedule'] ?? [],
            'workSchedules' => $workSchedules
        ]))->setWidth(12, 0);
    }
}
