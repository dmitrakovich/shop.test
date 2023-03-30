<?php

namespace App\Admin\Controllers\Config;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class NewsletterForm extends Form
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
    public $title = 'Рассылка для зарегистрированных';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $requestData = $request->all();
        $requestData['active'] = (isset($requestData['active']) && $requestData['active'] === 'on') ? true : false;
        Config::find('newsletter_register')->update(['config' => $requestData]);
        admin_success('Конфиг успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->switch('active', 'Включена')->states($this->states);
        $this->number('to_days', 'Количество дней после регистрации до')->default(5)->required();
        $this->number('from_days', 'Количество дней после регистрации от')->default(30)->required();
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('newsletter_register');
    }
}
