<?php

namespace App\Admin\Actions\Feedbacks;

use Encore\Admin\Actions\RowAction;

class ShowAnswersAction extends RowAction
{
    public $name = 'Все ответы';

    /**
     * @return  string
     */
    public function href()
    {
        return route('admin.feedbacks.feedback-answers.index', $this->getKey());
    }
}
