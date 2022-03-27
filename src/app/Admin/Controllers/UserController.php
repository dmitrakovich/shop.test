<?php

namespace App\Admin\Controllers;

use App\Models\Country;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Controllers\AdminController;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('first_name', 'Имя');
        $grid->column('last_name', 'Фамилия');
        $grid->column('patronymic_name', 'Отчество');
        $grid->column('email', __('Email'));
        $grid->column('phone', 'Телефон');
        // $grid->column('birth_date', 'Дата рождения');
        $grid->column('addresses', 'Адрес')->display(function ($addresses) {
            return $addresses[0]['address'] ?? null;
        });
        $grid->column('created_at', 'Дата регистрации');

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(50);

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
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('first_name', 'Имя');
        $form->text('last_name', 'Фамилия');
        $form->text('patronymic_name', 'Отчество');
        $form->email('email', __('Email'));
        $form->phone('phone', 'Телефон');
        $form->date('birth_date', 'Дата рождения')->default(date('Y-m-d'));

        $form->hasMany('addresses', 'Адреса', function (Form\NestedForm $form) {
            $form->select('country_id', 'Страна')->options(Country::pluck('name', 'id'));
            $form->textarea('address', 'Адрес');
        });

        return $form;
    }
}
