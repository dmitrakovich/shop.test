<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Feedbacks\ShowAnswersAction;
use App\Admin\Actions\RatingAction;
use App\Admin\Selectable\Product;
use App\Admin\Selectable\User;
use App\Models\Feedback;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class FeedbackController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Feedback';

    /**
     * Feedback types
     *
     * @var array
     */
    protected $feedbackTypes = [
        Feedback::TYPE_SPAM => 'спам',
        Feedback::TYPE_REVIEW => 'отзыв',
        Feedback::TYPE_QUESTION => 'вопрос',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Feedback);

        $grid->column('id', 'Ответить')->display(function ($feedbackId) {
            return '<a href="' . route('admin.feedbacks.feedback-answers.create', $feedbackId) . '" target="_blank">Ответить</a>';
        });
        $grid->column('user_name', __('Имя'));
        $grid->column('user_email', __('Email'))->email();
        $grid->column('user_city', 'Город');
        $grid->column('text', 'Текст')->limit(240);
        $grid->column('answers', 'Последний ответ')->display(function ($answers) {
            return empty($answers) ? null : Str::limit(end($answers)['text'], 240);
        });
        $grid->column('rating', 'Оценка')->action(RatingAction::class);
        $grid->column('product.title', 'Товар'); /*->display(function ($product) {
            return empty($product) ? 'не найден' : "<a href='$product[path]' target='_blank'>$product[title]</a>";
        });*/
        $grid->column('type_id', 'Тип')->using($this->feedbackTypes);
        $grid->column('publish', 'Публиковать')->switch();
        // $grid->column('ip', __('Ip'));
        $grid->column('created_at', 'Дата создния');

        $grid->model()->orderBy('id', 'desc');

        $grid->actions(function ($actions) {
            $actions->add(new ShowAnswersAction);
        });

        $grid->rows(function ($row) {
            if ($row->column('type_id') == 'спам') {
                $row->style('background-color:#cca4a4;');
            }
        });

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
        $form = new Form(new Feedback);

        $form->belongsTo('user_id', User::class, 'Пользователь');
        // $form->number('yandex_id', __('Yandex id'));
        $form->text('user_name', 'Имя')->required();
        $form->text('user_email', 'Email');
        // $form->number('user_phone', __('User phone'));
        $form->text('user_city', 'Город');
        $form->textarea('text', 'Текст')->required();
        $form->select('rating', 'Оценка')->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5])->default(5);
        $form->belongsTo('product_id', Product::class, 'Товар')->rules('required');
        $form->select('type_id', 'Тип')->options($this->feedbackTypes)->default(1);
        $form->switch('view_only_posted', __('View only posted'))->default(1);
        $form->switch('publish', __('Publish'))->default(1);
        $form->ip('ip', __('Ip'))->default(request()->ip())->readonly();
        $form->multipleImage('photos', 'Фото')->readonly();
        $form->multipleImage('videos', 'Видео')->readonly();

        return $form;
    }
}
