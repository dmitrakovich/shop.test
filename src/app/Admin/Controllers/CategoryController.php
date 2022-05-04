<?php

namespace App\Admin\Controllers;

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

        $grid->column('id', __('Id'));
        $grid->column('slug', __('Slug'));
        $grid->column('path', __('Path'));
        $grid->column('title', __('Title'));
        $grid->column('description', __('Description'));
        // $grid->column('_lft', __(' lft'));
        // $grid->column('_rgt', __(' rgt'));
        $grid->column('parent_id', __('Parent id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

        $grid->paginate(30);

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

        $form->text('slug', __('Slug'));
        $form->text('title', __('Title'));
        $form->textarea('description', __('Description'));
        // $form->number('_lft', __(' lft'));
        // $form->number('_rgt', __(' rgt'));
        $form->number('parent_id', __('Parent id'))->default(1);
        $form->hidden('path', __('Path'))->default(time());

        $form->saved(function (Form $form) {
            $form->model()->url()->updateOrCreate(['slug' => $form->slug]);
        });

        return $form;
    }
}
