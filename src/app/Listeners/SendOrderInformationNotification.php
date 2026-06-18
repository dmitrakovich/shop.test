<?php

namespace App\Listeners;

use App\Events\Order\OrderCreated;
use App\Mail\OrderCreated as OrderCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Sentry\State\Scope;

use function Sentry\captureException;
use function Sentry\withScope;

class SendOrderInformationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (!empty($order['email'])) {
            $this->sendMail($order['email'], $order);
        }

        if (App::environment('production')) {
            $this->sendMail(config('contacts.email.link', 'info@barocco.by'), $order);
        }
    }

    private function sendMail(string $recipient, object $order): void
    {
        try {
            Mail::to($recipient)->send(new OrderCreatedMail($order));
        } catch (\Throwable $e) {
            withScope(function (Scope $scope) use ($order, $recipient, $e): void {
                $scope->setContext('order_mail', [
                    'order_id' => $order->id ?? null,
                    'recipient' => $recipient,
                ]);
                captureException($e);
            });
        }
    }
}
