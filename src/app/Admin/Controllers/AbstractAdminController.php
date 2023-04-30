<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Show;

abstract class AbstractAdminController extends AdminController
{
    /**
     * Input value from presenter (stub for IDE).
     *
     * @var mixed
     */
    protected $input;

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return back();
    }
}
