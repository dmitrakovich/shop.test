<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Captcha
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->input('captcha_token');
        $captchaScore = 0;

        if (isset($token)) {
            $data = [
                'secret' => config('captcha.secret'),
                'response' => $token
            ];
            $response = Http::get(config('captcha.url'), $data);

            if ($response->ok() && $response->json('success')) {
                $captchaScore = (float)$response->json('score') * 10;
            }
        }

        $request->merge(['captcha_score' => $captchaScore]);

        return $next($request);
    }
}
