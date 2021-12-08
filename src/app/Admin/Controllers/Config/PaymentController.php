<?php

namespace App\Admin\Controllers\Config;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Payments\PaymentMethod;

class PaymentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PaymentMethod';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PaymentMethod());

        // $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('class', __('Class'));
        $grid->column('active', __('Active'))->switch();
        // $grid->column('sorting', __('Sorting'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(PaymentMethod::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('class', __('Class'));
        $show->field('active', __('Active'));
        $show->field('sorting', __('Sorting'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PaymentMethod());

        $form->text('name', __('Name'));
        $form->text('class', __('Class'));
        $form->switch('active', __('Active'));
        // $form->number('sorting', __('Sorting'));

        return $form;
    }
}
