<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
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
            'index_top' => 'На главной сверху',
            'index_bottom' => 'На главной снизу',
        ]);
        $grid->column('resource', 'Media')->image();;
        $grid->column('title', __('Title'));
        $grid->column('url', __('Url'));
        $grid->column('priority', 'Приоритет')->editable(); //->orderable();
        $grid->column('active', 'Активный')->switch();
        $grid->column('start_datetime', 'Дата начала');
        $grid->column('end_datetime', 'Дата оконания');
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
        // $banner = new Banner();
        // $banner->setAppends(['resource']);

        $form = new Form(new Banner());

        $form->select('position', 'Позиция')->options([
            'catalog_top' => 'В каталоге',
            'index_top' => 'На главной сверху',
            'index_bottom' => 'На главной снизу',
        ])->required();
        $form->image('resource', 'Media');
        $form->text('title', __('Title'));
        $form->text('url', __('Url'));
        $form->number('priority', 'Приоритет')->default(0);
        $form->switch('active', 'Активный')->default(1);
        $form->datetime('start_datetime', 'Дата начала')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_datetime', 'Дата оконания'); // ->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
