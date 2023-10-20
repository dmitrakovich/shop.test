<?php

namespace App\Admin\Controllers\Config;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class SendingTracksForm extends Form
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
    public $title = 'Отправка треков';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $requestData = $request->all();
        $requestData['active'] = (isset($requestData['active']) && $requestData['active'] === 'on') ? true : false;
        Config::find('sending_tracks')->update(['config' => $requestData]);
        admin_success('Конфиг успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->switch('active', 'Включена')->states($this->states);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('sending_tracks');
    }
}
