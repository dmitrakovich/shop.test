<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Config;
use App\Models\InfoPage;
use App\Models\User\Group;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

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
        $userDiscounts = Cache::rememberForever(config('cache_config.global_user_discounts.key'), function () {
            $registeredGroup = Group::where('id', Group::REGISTERED)->first();

            return [
                'registered' => $registeredGroup,
            ];
        });
        $installmentMinPrice3Parts = Config::findCacheable('installment')['min_price_3_parts'] ?? 150;
        View::share('g_navCategories', $navCategories);
        View::share('g_navInfoPages', $navInfoPages);
        View::share('g_userDiscounts', $userDiscounts);
        View::share('g_installmentMinPrice3Parts', $installmentMinPrice3Parts);

        return $next($request);
    }
}
