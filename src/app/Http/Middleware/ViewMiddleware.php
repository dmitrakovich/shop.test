<?php

namespace App\Http\Middleware;

use App\Enums\User\UserGroupTypeEnum;

use App\Models\Category;
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
        $userDiscounts = Cache::remember(config('cache_config.global_user_discounts.key'), 600, function () {
            $groups = Group::whereNotNull('enum_type_id')->get();
            return [
                'registered' => $groups->where('enum_type_id', UserGroupTypeEnum::REGISTERED)->first()
            ];
        });
        View::share('g_navCategories', $navCategories);
        View::share('g_navInfoPages', $navInfoPages);
        View::share('g_userDiscounts', $userDiscounts);

        return $next($request);
    }
}
