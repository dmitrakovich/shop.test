<?php

namespace App\Admin\Controllers\Config;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class InstallmentForm extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Рассрочка';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Конфигурация рассрочки';

    /**
     * Handle the form request.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        Config::find('installment')->update(['config' => $request->all()]);

        admin_success('Конфиг успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->currency('min_price', 'Минимальная сумма рассрочки')->symbol('BYN');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('installment');
    }
}
