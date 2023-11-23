<?php

namespace App\Admin\Controllers\ProductAttributes;

use App\Models\ProductAttributes\CountryOfOrigin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CountryOfOriginController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Страна производитель';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CountryOfOrigin());

        $grid->column('id', 'Id');
        $grid->column('name', 'Название');
        $grid->column('slug', 'Slug');
        $grid->column('seo', 'Seo');
        $grid->column('created_at', 'Дата создания')->display(fn() => date('d.m.Y H:i:s', strtotime($this->created_at)));
        $grid->column('updated_at', 'Дата обновления')->display(fn() => date('d.m.Y H:i:s', strtotime($this->updated_at)));

        $grid->disableFilter();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableRowSelector();

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
        $show = new Show(CountryOfOrigin::findOrFail($id));

        $show->field('id', 'Id');
        $show->field('name', 'Название');
        $show->field('slug', 'Slug');
        $show->field('seo', 'Seo');
        $show->field('created_at', 'Дата создания');
        $show->field('updated_at', 'Дата обновления');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CountryOfOrigin());

        $form->text('name', 'Название');
        $form->text('slug', 'Slug');
        $form->textarea('seo', 'Seo');

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
