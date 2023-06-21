<?php

namespace App\Admin\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

class UpdateAvailability extends AbstractTool
{
    public function render()
    {
        return view('admin.tools.update-availability');
    }
}
