<?php

namespace App\Admin\Controllers;

use App\Models\Sale;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SaleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sale';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Sale());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('label_text', __('Label text'));
        $grid->column('start_datetime', __('Start datetime'));
        $grid->column('end_datetime', __('End datetime'));
        $grid->column('algorithm', __('Algorithm'));
        $grid->column('sale', __('Sale'));
        $grid->column('categories', __('Categories'));
        $grid->column('collections', __('Collections'));
        $grid->column('styles', __('Styles'));
        $grid->column('seasons', __('Seasons'));
        $grid->column('only_new', __('Only new'));
        $grid->column('add_client_sale', __('Add client sale'));
        $grid->column('has_installment', __('Has installment'));
        $grid->column('has_fitting', __('Has fitting'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('deleted_at', __('Deleted at'));

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
        $show = new Show(Sale::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('label_text', __('Label text'));
        $show->field('start_datetime', __('Start datetime'));
        $show->field('end_datetime', __('End datetime'));
        $show->field('algorithm', __('Algorithm'));
        $show->field('sale', __('Sale'));
        $show->field('categories', __('Categories'));
        $show->field('collections', __('Collections'));
        $show->field('styles', __('Styles'));
        $show->field('seasons', __('Seasons'));
        $show->field('only_new', __('Only new'));
        $show->field('add_client_sale', __('Add client sale'));
        $show->field('has_installment', __('Has installment'));
        $show->field('has_fitting', __('Has fitting'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Sale());

        $form->text('title', __('Title'));
        $form->text('label_text', __('Label text'));
        $form->datetime('start_datetime', __('Start datetime'))->default(date('Y-m-d H:i:s'));
        $form->datetime('end_datetime', __('End datetime'))->default(date('Y-m-d H:i:s'));
        $form->text('algorithm', __('Algorithm'))->default('simple');
        $form->text('sale', __('Sale'));
        $form->textarea('categories', __('Categories'));
        $form->textarea('collections', __('Collections'));
        $form->textarea('styles', __('Styles'));
        $form->textarea('seasons', __('Seasons'));
        $form->switch('only_new', __('Only new'));
        $form->switch('add_client_sale', __('Add client sale'));
        $form->switch('has_installment', __('Has installment'))->default(1);
        $form->switch('has_fitting', __('Has fitting'))->default(1);

        return $form;
    }
}
