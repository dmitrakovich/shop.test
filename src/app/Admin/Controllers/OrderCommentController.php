<?php

namespace App\Admin\Controllers;

use App\Models\Orders\OrderAdminComment;
use Encore\Admin\Form;

class OrderCommentController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Комментарии менеджера';

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OrderAdminComment);

        $form->text('comment', 'Комментарий')->required();

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
