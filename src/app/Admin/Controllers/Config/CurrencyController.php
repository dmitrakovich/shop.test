<?php

namespace App\Admin\Controllers\Config;

use App\Models\Currency;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Currency';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Currency());

        $grid->column('code', 'Код валюты');
        $grid->column('country', 'Код страны');
        $grid->column('rate', 'Курс');
        // $grid->column('decimals', __('Decimals'));
        $grid->column('symbol', 'Знак');
        // $grid->column('icon', __('Icon'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

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
        $form = new Form(new Currency());

        $form->text('code', 'Код валюты')->required();
        $form->text('country', 'Код страны')->required();
        $form->decimal('rate', 'Курс')->required();
        $form->number('decimals', 'Кол-во отображаемых знаков после запятой');
        $form->text('symbol', 'Знак');
        // $form->text('icon', __('Icon'));

        $form->saved(function (Form $form) {
            Cache::forget('currencies');
        });

        return $form;
    }
}
