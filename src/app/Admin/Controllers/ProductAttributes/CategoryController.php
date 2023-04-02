<?php

namespace App\Admin\Controllers\ProductAttributes;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Category';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());

        $grid->column('id', 'Id');
        $grid->column('slug', 'Slug');
        $grid->column('path', 'Path');
        $grid->column('title', 'Название');
        $grid->column('description', 'Описание');
        $grid->column('parent_id', 'ID родительской категории');

        $grid->paginate(30);

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
        $form = new Form(new Category());

        $form->text('slug', 'Slug');
        $form->text('title', 'Название на сайте');
        $form->text('one_c_name', 'Название в 1С')->rules('unique:categories');
        $form->textarea('description', 'Описание');
        $form->number('parent_id', 'ID родительской категории')->default(1);
        $form->hidden('path', 'Path')->default(time());

        $form->saved(function (Form $form) {
            $form->model()->url()->updateOrCreate(['slug' => $form->slug]);
        });

        return $form;
    }
}
