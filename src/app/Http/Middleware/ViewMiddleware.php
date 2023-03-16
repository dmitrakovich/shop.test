<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\InfoPage;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class ViewMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $navCategories = Cache::rememberForever(
            config('cache_config.global_nav_categories.key'),
            function () {
                return Category::where('parent_id', 1)->get(['id', 'slug', 'title']);
            }
        );
        $navInfoPages = Cache::rememberForever(
            config('cache_config.global_nav_info_pages.key'),
            function () {
                return InfoPage::get(['slug', 'name', 'icon'])->toArray();
            }
        );
        View::share('g_navCategories', $navCategories);
        View::share('g_navInfoPages', $navInfoPages);
        return $next($request);
    }
}
