<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class RatingController extends AdminController
{
    public function __invoke(Content $content)
    {
        return $content
            ->title('рейтинг')
            ->view('legacy.rating', ['data' => 'foo']);
    }
}
