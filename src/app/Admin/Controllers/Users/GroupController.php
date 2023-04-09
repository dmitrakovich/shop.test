<?php

namespace App\Admin\Controllers\Users;

use App\Enums\User\UserGroupTypeEnum;
use App\Models\User\Group;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Группы пользователей';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Group());

        $grid->column('id', 'Идентификатор');
        $grid->column('name', 'Название');
        $grid->column('discount', 'Скидка')->suffix('%');
        $grid->column('enum_type_id', 'Тип группы')->display(fn () => $this->enum_type_id ? $this->enum_type_id->name() : null);

        $grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
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
        $form = new Form(new Group());

        $form->text('name', 'Название')->required();
        $form->decimal('discount', 'Скидка (%)')->default(0.00)->required();
        $form->select('enum_type_id', 'Тип группы')->options(UserGroupTypeEnum::list());

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
        });

        return $form;
    }
}
