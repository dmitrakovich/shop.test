<?php

namespace App\Admin\Controllers\Debug;

use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Artisan;
use Encore\Admin\Controllers\AdminController;

class CacheController extends AdminController
{
    public function __invoke(Content $content)
    {
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('event:cache');
        Artisan::call('opcache:clear');

        admin_success('Кэш успешно сброшен!');

        return $content
            ->title('Сброс кэша')
            ->body('All cache is cleared');
    }
}
