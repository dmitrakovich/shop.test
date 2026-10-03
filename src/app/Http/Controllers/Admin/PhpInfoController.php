<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class PhpInfoController extends Controller
{
    /**
     * Full phpinfo() output for checking PHP configuration on production.
     */
    public function __invoke(): Response
    {
        if (!function_exists('phpinfo')) {
            return response('phpinfo() is disabled.', headers: ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        ob_start();
        phpinfo();

        return response((string)ob_get_clean());
    }
}
