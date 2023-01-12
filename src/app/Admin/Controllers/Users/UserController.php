<?php

namespace App\Admin\Controllers\Users;

use App\Models\Country;
use App\Models\User\Group;
use App\Models\User\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Show;

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
        $grid->column('email', 'Email');
        $grid->column('phone', 'Телефон');
        $grid->column('orders', 'Сумма покупок')->display(fn () => $this->completedOrdersCost() . ' руб.');
        $grid->column('group.name', 'Группа');
        $grid->column('reviews', 'Кол-во отзывов')->display(fn ($reviews) => count($reviews));
        $grid->column('addresses', 'Адрес')->display(fn ($addresses) => $addresses[0]['address'] ?? null);
        $grid->column('created_at', 'Дата регистрации');

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(50);

        $grid->filter(function (Filter $filter) {
            $filter->like('first_name', 'Имя');
            $filter->like('last_name', 'Фамилия');
            $filter->like('patronymic_name', 'Отчество');
            $filter->like('phone', 'Телефон');
            $filter->equal('group_id', 'Группа')->select(Group::query()->pluck('name', 'id'));
            $filter->like('email', 'Email');
            $filter->like('addresses.city', 'Город');
            $filter->like('addresses.address', 'Адрес');
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
        $form = new Form(new User());

        $form->text('first_name', 'Имя')->required();
        $form->text('last_name', 'Фамилия')->required();
        $form->text('patronymic_name', 'Отчество');
        $form->email('email', 'Email');
        $form->phone('phone', 'Телефон')->required();
        $form->date('birth_date', 'Дата рождения')->default(date('Y-m-d'));
        $form->select('group_id', 'Группа')->options(Group::query()->pluck('name', 'id'))->required();

        $form->hasMany('addresses', 'Адреса', function (Form\NestedForm $form) {
            $form->select('country_id', 'Страна')->options(Country::query()->pluck('name', 'id'));
            $form->text('city', 'Город');
            $form->textarea('address', 'Адрес');
        });

        $form->hasMany('reviews', 'Отзывы', function (Form\NestedForm $form) {
            $form->textarea('text', 'Отзыв')->readonly();
        });

        return $form;
    }
}
