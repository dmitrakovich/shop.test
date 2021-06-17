<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Models\Media;
use Encore\Admin\Controllers\AdminController;

class MediaController extends AdminController
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

        // $grid->column('id', __('Id'));
        // $grid->column('model_type', __('Model type'));
        // $grid->column('model_id', __('Model id'));
        // $grid->column('model', __('Model'));

        $grid->column('picture')->display(function () {
            return $this->getUrl();
        })->image('', 120, 120);

        $grid->column('link', 'Ссылка на товар')->display(function () {
            return '<a href="' . $this->model->getUrl() . '" target="_blank">' . $this->model->getFullName() . '</a>';
        });
        // $grid->column('model.slug', 'Slug');
        // $grid->column('uuid', __('Uuid'));
        // $grid->column('collection_name', __('Collection name'));
        // $grid->column('name', __('Name'));
        // $grid->column('file_name', __('File name'));
        // $grid->column('mime_type', __('Mime type'));
        // $grid->column('disk', __('Disk'));
        // $grid->column('conversions_disk', __('Conversions disk'));
        // $grid->column('size', __('Size'));
        // $grid->column('manipulations', __('Manipulations'));
        // $grid->column('custom_properties', __('Custom properties'));
        $grid->column('video_url', 'Ссылка на видео')->display(function () {
            return $this->custom_properties['video'] ?? '';
        })->editable(); // ->copyable()
        // $grid->column('responsive_images', __('Responsive images'));
        $grid->column('order_column', 'Сортировка');
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        $grid->model()->orderBy('id', 'desc');
        // $grid->model()->where('custom_properties', 'like', '%video%');
        $grid->model()->where('model_type', 'App\Models\Product');
        $grid->model()->with(['model']);

        $grid->filter(function($filter) {
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
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Media::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('model_type', __('Model type'));
        $show->field('model_id', __('Model id'));
        $show->field('uuid', __('Uuid'));
        $show->field('collection_name', __('Collection name'));
        $show->field('name', __('Name'));
        $show->field('file_name', __('File name'));
        $show->field('mime_type', __('Mime type'));
        $show->field('disk', __('Disk'));
        $show->field('conversions_disk', __('Conversions disk'));
        $show->field('size', __('Size'));
        $show->field('manipulations', __('Manipulations'));
        $show->field('custom_properties', __('Custom properties'));
        $show->field('responsive_images', __('Responsive images'));
        $show->field('order_column', __('Order column'));
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
        $form = new Form(new Media());

        $form->submitted(function (Form $form) {
            $videoUrl = request()->input('video_url');
            if (isset($videoUrl)) {
                $form->model()->setCustomProperty('video', $videoUrl);
            } else {
                $form->model()->forgetCustomProperty('video');
            }
        });

        return $form;
    }
}
