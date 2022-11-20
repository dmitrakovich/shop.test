<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class AvailiabilityController extends AdminController
{
    public function __invoke(Content $content)
    {
        return $content
            ->title('Наличие')
            ->view('legacy.availability', ['data' => 'foo']);
    }
}
