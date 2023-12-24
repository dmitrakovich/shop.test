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
                        'user_id' => $item['user_id'],
                        'time_from' => $item['time_from'],
                        'time_to' => $item['time_to'],
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
        $this->table('schedule', 'Расписание', function ($table) use ($admins) {
            $table->select('user_id', 'Менеджер')->options($admins)->required();
            $table->time('time_from', 'Время работы с')->required();
            $table->time('time_to', 'Время работы до')->required();
        });
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
