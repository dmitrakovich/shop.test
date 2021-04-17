<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Models\Banner;
use Encore\Admin\Controllers\AdminController;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Banner';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        // $grid->column('id', __('Id'));
        $grid->column('position', 'Позиция')->using([
            'catalog_top' => 'В каталоге',
            'index_main' => 'На главной главный',
            'index_top' => 'На главной сверху',
            'index_bottom' => 'На главной снизу',
            'main_menu_catalog' => 'В главном меню | каталог'
        ]);
        $grid->column('resource', 'Media')->image();
        $grid->column('title', __('Title'))->display(function ($title) {
            return $title;
        });
        $grid->column('url', __('Url'));
        $grid->column('priority', 'Приоритет')->editable(); //->orderable();
        $grid->column('active', 'Активный')->switch();
        $grid->column('start_datetime', 'Дата начала');
        $grid->column('end_datetime', 'Дата окончания');
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

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
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('position', __('Position'));
        $show->field('title', __('Title'));
        $show->field('url', __('Url'));
        $show->field('priority', __('Priority'));
        $show->field('active', __('Active'));
        $show->field('start_datetime', __('Start datetime'));
        $show->field('end_datetime', __('End datetime'));
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
        $form = new Form(new Banner());

        $form->select('position', 'Позиция')->options([
            'catalog_top' => 'В каталоге',
            'index_main' => 'На главной главный',
            'index_top' => 'На главной сверху',
            'index_bottom' => 'На главной снизу',
            'main_menu_catalog' => 'В главном меню | каталог'
        ])->required();

        $form->radioButton('type','Тип баннера')
            ->options(['Картинка', 'Видео'])
            ->when(0, function (Form $form) {
                $form->image('resource', 'Баннер');
            })->when(1, function (Form $form) {
                $form->image('resource', 'Картинка для превью');
                $form->multipleFile('videos', 'Видео')->removable();
                // $form->image('video_mp4','Видео в формате mp4');
                // $form->image('video_webm','Видео в формате webm');
                // $form->image('video_ogv','Видео в формате ogv');
            })
            ->default($form->isCreating() ? 0 : null);

        $form->text('title', __('Title'));
        $form->text('url', __('Url'));
        $form->number('priority', 'Приоритет')->default(0);
        $form->switch('active', 'Активный')->default(1);
        $form->datetime('start_datetime', 'Дата начала')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_datetime', 'Дата оконания'); // ->default(date('Y-m-d H:i:s'));

        $form->submitted(function (Form $form) {
            $form->ignore('type');
        });

        return $form;
    }
}
