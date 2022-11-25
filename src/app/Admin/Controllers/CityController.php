<?php

namespace App\Admin\Controllers;

use App\Models\City;
use App\Models\Country;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class CityController extends AdminController
{
    protected $title    = 'Города';

    protected function grid()
    {
        $grid = new Grid(new City);
        $grid->model()->orderBy('id', 'desc');
        $countries = Country::pluck('name', 'id');

        $grid->filter(function ($filter) use ($countries) {
            $filter->like('name',     'Название города');
            $filter->in('country_id', 'Страна')->multipleSelect($countries);
            $filter->disableIdFilter();
        });

        $grid->column('id',                   'ID')->sortable();
        $grid->column('name',                 'Название');
        $grid->column('slug',                 'Slug');
        $grid->column('catalog_title',        'Seo текст (в каталоге)');

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });
        $grid->paginate(50);
        $grid->perPages([25, 50, 100, 500]);
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableRowSelector();
        return $grid;
    }

    protected function form()
    {
        $form      = new Form(new City);
        $countries = Country::pluck('name', 'id');

        $form->select('country_id',              'Страна')->options($countries);
        $form->text('name',                      'Название города')->placeholder('Введите название города')->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
        $form->text('catalog_title',             'Seo текст (в каталоге)')->placeholder('Введите seo текст');
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
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
