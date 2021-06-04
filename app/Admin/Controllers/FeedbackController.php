<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\RatingAction;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Feedback;
use App\Admin\Models\Product;
use Encore\Admin\Controllers\AdminController;

class FeedbackController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Feedback';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Feedback());

        // $grid->column('id', __('Id'));
        // $grid->column('user_id', __('User id'));
        // $grid->column('yandex_id', __('Yandex id'));
        $grid->column('user_name', __('Имя'));
        $grid->column('user_email', __('Email'))->email();
        $grid->column('user_phone', __('Телефон'));
        $grid->column('text', 'Текст')->limit(240);
        $grid->column('rating', 'Оценка')->action(RatingAction::class);
        $grid->column('product.title', 'Товар');/*->display(function ($product) {
            return empty($product) ? 'не найден' : "<a href='$product[path]' target='_blank'>$product[title]</a>";
        });*/
        $grid->column('type_id', 'Тип')->using([1 => 'отзыв', 2 => 'вопрос']);;
        // $grid->column('view_only_posted', __('View only posted'));
        $grid->column('publish', 'Публиковать')->switch();
        // $grid->column('ip', __('Ip'));
        $grid->column('created_at', 'Дата создния');
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

        $grid->model()->orderBy('id', 'desc');

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
        $show = new Show(Feedback::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('yandex_id', __('Yandex id'));
        $show->field('user_name', __('User name'));
        $show->field('user_email', __('User email'));
        $show->field('user_phone', __('User phone'));
        $show->field('text', __('Text'));
        $show->field('rating', __('Rating'));
        $show->field('product_id', __('Product id'));
        $show->field('type_id', __('Type id'));
        $show->field('view_only_posted', __('View only posted'));
        $show->field('publish', __('Publish'));
        $show->field('ip', __('Ip'));
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
        $form = new Form(new Feedback());

        $form->number('user_id', __('User id'));
        $form->number('yandex_id', __('Yandex id'));
        $form->text('user_name', __('User name'));
        $form->text('user_email', __('User email'));
        $form->number('user_phone', __('User phone'));
        $form->textarea('text', __('Text'));
        $form->switch('rating', __('Rating'));
        $form->number('product_id', __('Product id'));
        $form->switch('type_id', __('Type id'))->default(1);
        $form->switch('view_only_posted', __('View only posted'))->default(1);
        $form->switch('publish', __('Publish'))->default(1);
        $form->ip('ip', __('Ip'));

        return $form;
    }
}
