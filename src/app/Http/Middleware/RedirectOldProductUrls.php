<?php

namespace App\Http\Middleware;

use App\Models\Url;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class RedirectOldProductUrls
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lastSlug = last($request->segments());

        $url = Url::query()->firstWhere('slug', $lastSlug);

        if ($url?->isProduct()) {
            /** @var \App\Models\Product $product */
            $product = $url->model;

            return redirect()->route('api.product.show', $product->slug, HttpResponse::HTTP_MOVED_PERMANENTLY);
        }

        return $next($request);
    }
}
