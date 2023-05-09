<?php

namespace App\Admin\Controllers\ProductAttributes;

use App\Models\Tag;
use App\Models\TagGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TagGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Группы тегов';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TagGroup());
        $grid->model()->orderBy('id', 'desc');

        $grid->filter(function ($filter) {
            $filter->like('name', 'Название группы');
            $filter->disableIdFilter();
        });

        $grid->column('name', 'Название группы');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->paginate(50);
        $grid->perPages([25, 50, 100, 500]);
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
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TagGroup());
        $tags = Tag::whereNull('tag_group_id')->pluck('name', 'id');

        $form->text('name', 'Название группы');
        $form->multipleSelect('tags', 'Теги')->options($tags);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
