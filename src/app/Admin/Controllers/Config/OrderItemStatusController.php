<?php

namespace App\Admin\Controllers\Config;

use App\Models\Orders\OrderItemStatus;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderItemStatusController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'OrderItemStatus';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderItemStatus);

        $grid->sortable();

        $grid->column('key', 'Slug');
        $grid->column('name_for_admin', 'Название для админки');
        $grid->column('name_for_user', 'Название для клиента');

        return $grid;
    }

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
        $form = new Form(new OrderItemStatus);

        if ($form->isCreating()) {
            $form->text('key', 'Slug');
        } else {
            $form->display('key', 'Slug');
        }
        $form->text('name_for_admin', 'Название для админки');
        $form->text('name_for_user', 'Название для клиента');

        return $form;
    }
}
