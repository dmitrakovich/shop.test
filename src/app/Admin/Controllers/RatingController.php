<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class RatingController extends AdminController
{
    public function __invoke(Content $content)
    {
        return $content
            ->title('Рейтинг')
            ->description('Конфигурация')
            ->view('admin.rating-config');
    }
}
