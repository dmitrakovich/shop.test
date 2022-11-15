<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class SkladController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title('Склад')
            ->view('legacy.sklad', ['data' => 'foo']);
    }

    public function export()
    {
    }
}
