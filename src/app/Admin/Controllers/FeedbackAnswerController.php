<?php

namespace App\Admin\Controllers;

use App\Models\FeedbackAnswer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FeedbackAnswerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'FeedbackAnswer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeedbackAnswer());

        $grid->column('id', __('Id'));
        $grid->column('feedback_id', __('Feedback id'));
        $grid->column('admin_id', __('Admin id'));
        $grid->column('text', __('Text'));
        $grid->column('publish', __('Publish'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('deleted_at', __('Deleted at'));

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
        $show = new Show(FeedbackAnswer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('feedback_id', __('Feedback id'));
        $show->field('admin_id', __('Admin id'));
        $show->field('text', __('Text'));
        $show->field('publish', __('Publish'));
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
        $form = new Form(new FeedbackAnswer());

        $form->id('feedback_id', 'id отзыва')->default(request('feedback'));
        $form->hidden('admin_id', 'id админа')->default(auth()->id());
        $form->textarea('text', 'Текст');
        $form->switch('publish', 'Публиковать')->default(true);

        return $form;
    }
}
