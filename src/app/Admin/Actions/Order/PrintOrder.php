<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class PrintOrder extends RowAction
{
    public $name = 'Печать';

    public function handle(Model $model)
    {
        return $this->response();
    }

    public function actionScript()
    {
        return self::printScript($this->getKey());
    }

    /**
     * Generate JS for print order
     *
     * @param integer $orderId
     * @return string
     */
    public static function printScript(int $orderId): string
    {
        return <<<JS
            (function () {
                let winWidth = 900;
                let winHeight = 800;
                let printWindow = window.open(
                    '/admin/orders/{$orderId}/print',
                    '_blank',
                    'width=' + winWidth
                    + ',height=' + winHeight
                    + ',left=' + (screen.width / 2 - winWidth / 2)
                    + ',top=' + (screen.height / 2 - winHeight / 2)
                );
                printWindow.print();
            }());
JS;
    }
}
