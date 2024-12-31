<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Banner;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Баннеры';

    private $position = [
        'catalog_top' => 'В каталоге',
        'index_main' => 'На главной главный',
        'index_top' => 'На главной сверху',
        'index_bottom' => 'На главной снизу',
        'main_menu_catalog' => 'В главном меню | каталог',
        'catalog_mob' => 'В каталоге моб.',
        'feedback' => 'В отзывах.',
        'feedback_mob' => 'В отзывах моб.',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        $grid->column('position', 'Позиция')->using($this->position);
        $grid->column('resource', 'Media')->image();
        $grid->column('title', 'Заголовок')->display(function ($title) {
            return $title;
        });
        $grid->column('url', __('Url'));
        $grid->column('priority', 'Приоритет')->editable(); // ->orderable();
        $grid->column('active', 'Активный')->switch();
        $grid->column('start_datetime', 'Дата начала');
        $grid->column('end_datetime', 'Дата окончания');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableColumnSelector();
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

        $form->select('position', 'Позиция')->options($this->position)->when('in', ['catalog_top', 'catalog_mob', 'index_main'], function (Form $form) {
            $form->radio('spoiler.show', 'Спойлер')
                ->options([
                    true => 'Да',
                    false => 'Нет',
                ])->when(1, function (Form $form) {
                    $form->embeds('spoiler', 'Описание спойлера', function ($form) {
                        $form->text('btn_name', 'Название кнопки');
                        $form->ckeditor('terms', 'Условия акции');
                        $form->color('text_color', 'Цвет текста')->default('#fff');
                        $form->color('bg_color', 'Цвет фона')->default('#D22020');
                    });
                });
        })->required();

        $form->radioButton('type', 'Тип баннера')
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

        $form->text('title', 'Заголовок');
        $form->text('url', __('Url'));
        $form->number('priority', 'Приоритет')->default(0);
        $form->switch('active', 'Активный')->default(1);
        $form->datetime('start_datetime', 'Дата начала')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_datetime', 'Дата оконания'); // ->default(date('Y-m-d H:i:s'));
        $form->radio('show_timer', 'Таймер')
            ->options([
                true => 'Да',
                false => 'Нет',
            ]);

        $form->submitted(function (Form $form) {
            $form->ignore('type');
        });

        $form->tools(function (Form\Tools $tools) {
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
