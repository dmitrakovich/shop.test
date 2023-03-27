<?php

namespace App\Admin\Controllers\Config;

use App\Models\Api\Token;
use App\Services\InstagramService;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InstagramTokenForm extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Токен для Instagram';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Ручное обновление токена для instagram';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $token = $request->input('token');
        $ttl = now()->addDays(InstagramService::TTL_DAYS);

        Token::instagram()->updateToken($token, $ttl);
        Cache::forget(InstagramService::CACHE_POSTS_KEY);

        admin_success('Токен успешно обновлен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->textarea('token', 'Токен')->required();
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return ['token' => Token::instagram()->first()];
    }
}
