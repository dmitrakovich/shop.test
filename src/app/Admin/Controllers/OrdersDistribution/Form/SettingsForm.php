<?php

namespace App\Admin\Controllers\OrdersDistribution\Form;

use App\Models\Config;
use App\Services\AdministratorService;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class SettingsForm extends Form
{
    public $title = 'Настройки';

    protected $states = [
        'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'danger'],
    ];

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $scheduleData = $request->all()['schedule'] ?? [];
        Config::find('distrib_order_setup')->update(['config' => [
            'active' => ($request->input('active') == 'on') ? true : false,
            'schedule' => array_map(
                function ($item) {
                    return [
                        'admin_user_id' => (int)$item['admin_user_id'],
                        'time_to_even' => $item['time_to_even'],
                        'time_from_even' => $item['time_from_even'],
                        'time_to_odd' => $item['time_to_odd'],
                        'time_from_odd' => $item['time_from_odd'],
                    ];
                },
                array_filter(array_values($scheduleData), function ($item) {
                    $remove = (int)($item['_remove_'] ?? 0);

                    return ($remove !== 1) ? true : false;
                })
            ),
        ]]);
        admin_success('Настройки успешно сохранены!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $admins = app(AdministratorService::class)->getAdministratorList();
        $this->switch('active', 'Распределение заказов включено')->states($this->states);
        $this->divider('Расписание');
        $this->table('schedule', ' ', function ($table) use ($admins) {
            $table->select('admin_user_id', 'Менеджер')->options($admins)->required();
            $table->timeRange('time_to_even', 'time_from_even', 'Время работы (четные дни)')->required();
            $table->timeRange('time_to_odd', 'time_from_odd', 'Время работы (нечетные дни)')->required();
        })->setWidth(12, 0);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        $config = Config::findCacheable('distrib_order_setup');

        return [
            'active' => (bool)($config['active'] ?? false),
            'schedule' => (array)($config['schedule'] ?? []),
        ];
    }
}
