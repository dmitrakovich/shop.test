<?php

namespace App\Admin\Controllers;

use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;

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
