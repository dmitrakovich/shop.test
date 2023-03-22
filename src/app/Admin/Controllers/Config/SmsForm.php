<?php

namespace App\Admin\Controllers\Config;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class SmsForm extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Смс';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Настройка отправки';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        Config::find('sms')->update(['config' => $request->all()]);

        admin_success('Конфиг успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->switch('enabled', 'Включено');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('sms');
    }
}
