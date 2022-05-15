<?php

namespace App\Http\Middleware;

use Closure;
use Sentry\State\Scope;
use Illuminate\Http\Request;

use function Sentry\configureScope;
use Illuminate\Support\Facades\Auth;

class SentryContext
{
    /**
     * Handle an incoming request.
     *
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
