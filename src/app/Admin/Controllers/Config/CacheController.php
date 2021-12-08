<?php

namespace App\Admin\Controllers\Config;

use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Artisan;
use Encore\Admin\Controllers\AdminController;

class CacheController extends AdminController
{
    public function __invoke(Content $content)
    {
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        admin_success('Кэш успешно сброшен!');

        return $content
            ->title('Сброс кэша')
            ->body('All cache is cleared');
    }
}
