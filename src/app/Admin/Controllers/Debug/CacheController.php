<?php

namespace App\Admin\Controllers\Debug;

use Appstract\Opcache\OpcacheFacade as OPcache;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Artisan;

class CacheController extends AdminController
{
    public function __invoke(Content $content)
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');
        OPcache::clear();

        admin_success('Кэш успешно сброшен!');

        return $content
            ->title('Сброс кэша')
            ->body('All cache is cleared');
    }
}
