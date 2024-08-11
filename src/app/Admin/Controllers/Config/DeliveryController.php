<?php

namespace App\Admin\Controllers\Config;

use App\Admin\Controllers\AbstractAdminController;
use Deliveries\DeliveryMethod;
use Encore\Admin\Form;
use Encore\Admin\Grid;

/**
 * @mixin DeliveryMethod
 */
class DeliveryController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DeliveryMethod';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryMethod);

        // $grid->column('id', __('Id'));
        $grid->column('name', 'Название способа доставки');
        $grid->column('instance_class', 'Instance')->display(fn () => $this->getRawOriginal('instance'));
        $grid->column('active', __('Active'))->switch();
        // $grid->column('sorting', __('Sorting'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DeliveryMethod);

        $form->text('name', __('Name'));
        // $form->text('instance', 'Instance');
        $form->switch('active', __('Active'));
        // $form->number('sorting', __('Sorting'));

        return $form;
    }
}
