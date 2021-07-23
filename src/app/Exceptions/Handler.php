<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Facades\Agent;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Максимальная длина сообщения
     */
    const MAX_MESSAGE_LENGTH = 4000;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function report(Throwable $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        } else {
            $this->sendInTelegram($e);
        }

        parent::report($e);
    }

    private function sendInTelegram (Throwable $e)
    {
        $time = Carbon::now()->format('Y-m-d H:i:s.u');
        $exception = get_class($e);

        $request = request();
        if ($request instanceof Request) {
            $url = "<code>{$request->method()}:</code> <a>{$request->fullUrl()}</a>";
            if ($request->isMethod('post')) {
                $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
                $postData = json_encode($request->input(), $jsonOptions);
                $postData = $postData = strtr($postData, ['\\\\\\\\' => '\\', '\\\\' => '\\', '\"' => '\'']);
                $url .= "\n<b>Post params:</b> <pre>$postData</pre>";
            }
        } else {
            $url = 'unknown';
        }

        $browser = Agent::platform() . ' | ' . Agent::browser();

        $traces = $e->getTrace() ?? null;
        $traceMessage = '';
        foreach ($traces as $num => $trace) {
            if ($num > 7)
                break;
            $file = isset($trace['file']) ? substr($trace['file'], 31) : '';
            $line = $trace['line'] ?? '';
            $function = $trace['function'] ?? '';
            $class = $trace['class'] ?? '';
            $traceMessage .= <<<MSG
                <b>#{$num}</b>
                    <b>file:</b> {$file}
                    <b>line:</b> {$line}
                    <b>function:</b> {$function}
                    <b>class:</b>{$class}\n
                MSG;
        }
        $host = request()->getHttpHost();
        $message = <<<MSG
            <b>Server:</b>          <code>{$host}</code>
            <b>Time:</b>             <code>{$time}</code>
            <b>Exception:</b>    <code>{$exception}</code>
            <b>Browser:</b>      <code>{$browser}</code>
            <b>Url:</b>                 {$url}
            <b>Line:</b>                <code>{$e->getLine()}</code>
            <b>Message:</b> <pre>{$e->getMessage()}</pre>
            <b>File:</b> <pre>{$e->getFile()}</pre>
            <b>Trace:</b> <pre>{$traceMessage}</pre>
            MSG;

        if (strlen($message) > self::MAX_MESSAGE_LENGTH) {
            $message = '<pre>' . substr(strip_tags($message), 0, self::MAX_MESSAGE_LENGTH - 15) . '...</pre>';
        }

        try {
            Telegram::sendMessage([
                'chat_id' => config('telegram.chat_id_for_errors'),
                'text' => $message,
                'parse_mode' => 'html'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
