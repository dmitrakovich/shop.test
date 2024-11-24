<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Show;
use Illuminate\Support\Carbon;

#[\AllowDynamicProperties]
abstract class AbstractAdminController extends AdminController
{
    /**
     * Input value from presenter (stub for IDE).
     *
     * @var mixed
     */
    protected $input;

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
        return back();
    }

    /**
     * Format the given date string as 'd.m.Y'.
     */
    protected static function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)
            ->setTimezone(config('app.timezone'))
            ->format('d.m.Y');
    }

    /**
     * Format the given datetime string as 'd.m.Y H:i:s'.
     */
    protected static function formatDateTime(?string $datetime): ?string
    {
        if (!$datetime) {
            return null;
        }

        return Carbon::parse($datetime)
            ->setTimezone(config('app.timezone'))
            ->format('d.m.Y H:i:s');
    }
}
