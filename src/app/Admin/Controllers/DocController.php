<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Models\Doc;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;


class DocController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Документация';

    /**
     * Show single doc page
     *
     * @return string
     */
    public function __invoke(Doc $doc, Content $content)
    {
        return $content->row($doc->html);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Doc());

        $grid->column('id', __('Id'));
        $grid->column('slug', __('Slug'));
        $grid->column('html', __('Html'))->display(function($text) {
            return Str::limit(strip_tags($text), 300, '...');
        })->style('max-width: 35vw;');
        $grid->column('created_at', 'Дата создния');
        $grid->column('updated_at', 'Дата редактирования');

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
        $show = new Show(Doc::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('slug', __('Slug'));
        $show->field('html', __('Html'));
        $show->field('created_at', 'Дата создния');
        $show->field('updated_at', 'Дата редактирования');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Doc());

        $form->text('slug', __('Slug'));
        $form->ckeditor('html', __('Html'));

        return $form;
    }
}
