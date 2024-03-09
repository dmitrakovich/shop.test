<?php

namespace App\Admin\Controllers\Orders;

use App\Admin\Controllers\AbstractAdminController;
use App\Models\OneC\OfflineOrder;
use Encore\Admin\Form;
use Encore\Admin\Grid;

/**
 * @mixin OfflineOrder
 */
class OfflineOrderController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Оффлайн заказы';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OfflineOrder());

        $grid->column('ROW_ID', __('ROW ID'));
        $grid->column('ID', __('ID'));
        $grid->column('CODE', __('CODE'));
        $grid->column('DESCR', __('DESCR'));
        $grid->column('ISMARK', __('ISMARK'));
        $grid->column('VERSTAMP', __('VERSTAMP'));
        $grid->column('SP6089', __('SP6089'));
        $grid->column('SP6090', __('SP6090'));
        $grid->column('SP6091', __('SP6091'));
        $grid->column('SP6092', __('SP6092'));
        $grid->column('SP6093', __('SP6093'));
        $grid->column('SP6094', __('SP6094'));
        $grid->column('SP6095', __('SP6095'));
        $grid->column('SP6096', __('SP6096'));
        $grid->column('SP6097', __('SP6097'));
        $grid->column('SP6098', __('SP6098'));
        $grid->column('SP6099', __('SP6099'));
        $grid->column('SP6100', __('SP6100'));
        $grid->column('SP6101', __('SP6101'));
        $grid->column('SP6102', __('SP6102'));

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OfflineOrder());

        $form->number('ROW_ID', __('ROW ID'));
        $form->text('ID', __('ID'));
        $form->text('CODE', __('CODE'));
        $form->text('DESCR', __('DESCR'));
        $form->switch('ISMARK', __('ISMARK'));
        $form->number('VERSTAMP', __('VERSTAMP'));
        $form->text('SP6089', __('SP6089'));
        $form->text('SP6090', __('SP6090'));
        $form->text('SP6091', __('SP6091'));
        $form->decimal('SP6092', __('SP6092'));
        $form->text('SP6093', __('SP6093'));
        $form->text('SP6094', __('SP6094'));
        $form->text('SP6095', __('SP6095'));
        $form->decimal('SP6096', __('SP6096'));
        $form->datetime('SP6097', __('SP6097'))->default(date('Y-m-d H:i:s'));
        $form->text('SP6098', __('SP6098'));
        $form->decimal('SP6099', __('SP6099'));
        $form->decimal('SP6100', __('SP6100'));
        $form->decimal('SP6101', __('SP6101'));
        $form->text('SP6102', __('SP6102'));

        return $form;
    }
}
