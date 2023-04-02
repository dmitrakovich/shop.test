<?php

namespace App\Admin\Controllers;

use App\Enums\StockTypeEnum;
use App\Models\City;
use App\Models\Stock;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Склады / Магазины';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Stock());
        $grid->sortable();

        $grid->filter(function ($filter) {
            $filter->in('type', 'Тип')->multipleSelect(StockTypeEnum::list());
            $filter->disableIdFilter();
        });

        $grid->column('id', 'Id');
        $grid->column('type', 'Тип')->display(fn () => $this->type->name());
        $grid->column('name', 'Название');
        $grid->column('internal_name', 'Внутреннее название');
        $grid->column('city.name', 'Город');
        $grid->column('address', 'Адрес');
        $grid->column('worktime', 'Время работы');
        $grid->column('phone', 'Телефон');
        $grid->column('geo_latitude', 'Координаты (широта)');
        $grid->column('geo_longitude', 'Координаты (долгота)');
        $grid->column('check_availability', 'Сверка наличия')->switch();

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();
        $grid->paginate(50);

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
        $form = new Form(new Stock());

        $form->number('one_c_id', 'ID в 1C')->min(1)->rules('required|unique:stocks');
        $form->select('type', 'Тип')->options(StockTypeEnum::list());
        $form->select('city_id', 'Город')->options(City::pluck('name', 'id'));
        $form->text('name', 'Название')->rules('required');
        $form->text('internal_name', 'Внутреннее название')->rules('required');
        $form->text('address', 'Адрес');
        $form->text('worktime', 'Время работы');
        $form->phone('phone', 'Телефон');
        $form->text('geo_latitude', 'Координаты (широта)');
        $form->text('geo_longitude', 'Координаты (долгота)');
        $form->switch('check_availability', 'Сверка наличия');
        $form->multipleImage('photos', 'Фото магазина')->sortable()->removable();

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
