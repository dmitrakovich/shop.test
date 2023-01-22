<?php

namespace App\Admin\Selectable;

use App\Models\User\User as UserModel;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class User extends Selectable
{
    public $model = UserModel::class;

    public function make()
    {
        $this->column('id', 'Id');
        $this->column('first_name', 'Имя');
        $this->column('last_name', 'Фамилия');
        $this->column('patronymic_name', 'Отчество');
        $this->column('phone', 'Номер телефона');

        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('first_name', 'Имя');
            $filter->like('last_name', 'Фамилия');
            $filter->like('patronymic_name', 'Отчество');
            $filter->like('phone', 'Телефон');
        });
    }
}
