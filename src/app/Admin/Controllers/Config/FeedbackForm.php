<?php

namespace App\Admin\Controllers\Config;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class FeedbackForm extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Отзывы';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        Config::find('feedback')->update(['config' => $request->all()]);

        admin_success('Конфиг успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->decimal('discount.BYN', 'Скидка (BYN)')->default(10)->required();
        $this->decimal('discount.USD', 'Скидка (USD)')->default(5)->required();
        $this->decimal('discount.KZT', 'Скидка (KZT)')->default(1500)->required();
        $this->decimal('discount.RUB', 'Скидка (RUB)')->default(350)->required();
        $this->number('send_after', 'Отправлять смс через (часов)')->default(72)->required();
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('feedback');
    }
}
