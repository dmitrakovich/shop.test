<?php

namespace App\Admin\Controllers;

use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;

class RatingController extends AdminController
{
    public function __invoke(Content $content)
    {
        return $content
            ->title('рейтинг')
            ->view('legacy.rating', ['data' => 'foo']);
    }
}
