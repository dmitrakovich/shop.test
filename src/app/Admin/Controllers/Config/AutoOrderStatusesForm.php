<?php

namespace App\Admin\Controllers\Config;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class AutoOrderStatusesForm extends Form
{
    protected $states = [
        'on' => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'danger'],
    ];

    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Автоматические статусы заказа';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $requestData = $request->all();
        $requestData['active'] = (isset($requestData['active']) && $requestData['active'] === 'on') ? true : false;
        $requestData['belpost_parse_email'] = (isset($requestData['belpost_parse_email']) && $requestData['belpost_parse_email'] === 'on') ? true : false;
        Config::find('auto_order_statuses')->update(['config' => $requestData]);
        admin_success('Конфиг успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->switch('active', 'Включено')->states($this->states);
        $this->switch('belpost_parse_email', 'Email автопарсинг (БелПочта)')->states($this->states);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('auto_order_statuses');
    }
}
