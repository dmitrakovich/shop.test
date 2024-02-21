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
        $this->currency('min_price_3_parts', 'Минимальная сумма на 3 платежа')->symbol('BYN');
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
