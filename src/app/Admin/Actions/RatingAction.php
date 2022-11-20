<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class RatingAction extends RowAction
{
    public function handle(Model $model)
    {
        // Switch the value of the `star` field and save
        // $model->rating = (int) !$model->rating;
        // $model->save();

        return $this->response()->html($this->renderHtml($model->rating));
    }

    /**
     * This method displays different icons in this column based on the value of the `star` field.
     *
     * @param  int  $stars
     * @return string
     */
    public function display($stars)
    {
        return $this->renderHtml($stars);
    }

    /**
     * render stars html
     *
     * @param  int  $star
     * @return string
     */
    protected function renderHtml(int $stars)
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $stars ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star-o"></i>';
        }

        return '<span style="white-space: nowrap;">' . $html . '</span>';
    }
}
