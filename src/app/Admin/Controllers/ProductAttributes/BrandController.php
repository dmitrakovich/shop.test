<?php

namespace App\Admin\Controllers\ProductAttributes;

use App\Models\Brand;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BrandController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Brand';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Brand);

        $grid->column('id', 'Id')->sortable();
        $grid->column('name', 'Name')->sortable();
        $grid->column('slug', 'Slug')->sortable();
        $grid->column('seo', 'Seo');

        $grid->paginate(100);

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
        $form = new Form(new Brand);

        $form->number('one_c_id', 'ID в 1С')->min(1)->rules('unique:brands,one_c_id,{{id}}');
        $form->text('name', 'Name');
        $form->text('slug', 'Slug');
        $form->textarea('seo', 'Seo');

        $form->saved(function (Form $form) {
            $form->model()->url()->updateOrCreate(['slug' => $form->slug]);
        });

        return $form;
    }
}
