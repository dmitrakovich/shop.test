<?php

namespace App\Admin\Controllers;

use App\Models\InfoPage;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class InfoPageController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'InfoPage';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new InfoPage());

        $grid->column('id', __('Id'));
        $grid->column('slug', __('Slug'));
        $grid->column('name', __('Name'))->label();
        $grid->column('icon', __('Icon'))->image(url('/'));
        $grid->column('html', __('Html'))->display(function ($text) {
            return Str::limit(strip_tags($text), 300, '...');
        });
        $grid->column('created_at', 'Дата создания')->display(fn ($datetime) => self::formatDateTime($datetime));
        $grid->column('updated_at', 'Дата обновления')->display(fn ($datetime) => self::formatDateTime($datetime));

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
        $show = new Show(InfoPage::query()->findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('slug', __('Slug'));
        $show->field('name', __('Name'));
        $show->field('icon', __('Icon'));
        $show->field('html', __('Html'));
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
        $form = new Form(new InfoPage());

        $form->text('slug', __('Slug'));
        $form->text('name', __('Name'));
        $form->text('icon', __('Icon'));
        $form->ckeditor('html', __('Html'));

        return $form;
    }
}
