<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Media;
use Encore\Admin\Form;
use Encore\Admin\Grid;

/**
 * @mixin Media
 */
class MediaController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Media';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Media());

        $grid->column('picture')->display(function () {
            return $this->getUrl();
        })->image('', 120, 120);
        $grid->column('link', 'Ссылка на товар')->display(function () {
            return '<a href="' . $this->model->getUrl() . '" target="_blank">' . $this->model->getFullName() . '</a>';
        });
        $grid->column('video_url', 'Ссылка на видео')->display(function () {
            return $this->custom_properties['video'] ?? '';
        })->editable();
        $grid->column('is_imidj', 'Имиджевое')->display(function () {
            return $this->custom_properties['is_imidj'] ?? false;
        })->switch();
        $grid->column('order_column', 'Сортировка');

        $grid->model()->orderBy('id', 'desc');
        // $grid->model()->where('custom_properties', 'like', '%video%');
        $grid->model()->where('model_type', 'App\Models\Product');
        $grid->model()->with(['model']);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter(); // Remove the default id filter
            $filter->where(function ($query) {
                $query->whereRaw("EXISTS (
                    SELECT * FROM `products`
                    WHERE `media`.`model_id` = `products`.`id`
                    AND `title` LIKE '%{$this->input}%'
                    AND `products`.`deleted_at` IS NULL )");
            }, 'Артикул');
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableCreateButton();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Media());

        $form->submitted(function (Form $form) {
            $videoUrl = request()->input('video_url');
            if (isset($videoUrl)) {
                $form->model()->setCustomProperty('video', $videoUrl);
            } else {
                $form->model()->forgetCustomProperty('video');
            }

            $isImidj = request()->input('is_imidj');
            if (!empty($isImidj)) {
                $form->model()->setCustomProperty('is_imidj', true);
            } else {
                $form->model()->forgetCustomProperty('is_imidj');
            }
        });

        return $form;
    }
}
