<?php

namespace App\Admin\Tools;

use App\Admin\Actions\Order\CreateOnlinePayment;
use Encore\Admin\Grid\Tools\AbstractTool;

class CreateOnlinePaymentTool extends AbstractTool
{
    public function __construct(
        private readonly int $orderId
    ) {}

    public function render(): string
    {
        $action = new CreateOnlinePayment($this->orderId);
        $content = $action->render();

        // PHP 8.5 + laravel-admin may return empty from Action::render() in some contexts.
        // Keep script registration side-effects from render(), but force button HTML fallback.
        if ($content) {
            return $content;
        }

        $modalId = strtolower(str_replace('\\', '-', get_class($action)));
        $html = $action->html();

        if (str_contains($html, ' modal=')) {
            return $html;
        }

        return preg_replace('/<a\s+/i', '<a modal="' . $modalId . '" ', $html, 1) ?? $html;
    }
}
