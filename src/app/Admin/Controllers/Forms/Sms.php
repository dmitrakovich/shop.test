<?php

namespace App\Admin\Controllers\Forms;

use App\Services\LogService;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Facades\Admin;
use Illuminate\Notifications\Facades\SmsTraffic;

class Sms extends Form
{
    /**
     *
     */
    const ROUTE_OPTIONS = [
        'sms' => 'SMS',
        'viber' => 'Viber',
        'viber(60)-sms' => 'Vb/SMS',
    ];

    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Отправить Vb/SMS';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, LogService $logService)
    {
        $phone = $request->input('phone');
        $text = $request->input('text');
        $routes = array_flip(self::ROUTE_OPTIONS);
        $route = $routes[$request->input('route')];

        $response = SmsTraffic::send($phone, $text, ['route' => $route]);
        $logService->logSms($phone, $text, $route, Admin::user()->id, null, $response->getDescription());

        admin_success('Сообщение отправлено. Id сообщения: ' . $response->getSmsId());

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->phone('phone', 'Номер телефона')->rules('required');
        $this->textarea('text', 'Текст сообщения')->rules('required');
        $this->select('route', 'Тип отправки')->options(self::ROUTE_OPTIONS)->rules('required');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return [
            'phone' => '',
            'text' => '',
            'route' => 'viber(60)-sms',
        ];
    }
}
