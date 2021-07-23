<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;

use function Sentry\configureScope;

class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (app()->bound('sentry')) {
            configureScope(function (Scope $scope) use ($request) {
                $scope->setTag('ip', $request->ip());
                // $scope->setContext('cookies', (array)$request->cookies);
                if (Auth::check()) {
                    $scope->setUser(Auth::user()->toArray());
                }
            });
        }

        return $next($request);
    }
}
