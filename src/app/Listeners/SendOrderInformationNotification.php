<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderCreated as OrderCreatedMail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendOrderInformationNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (!empty($order['email'])) {
            Mail::to($order['email'])->send(new OrderCreatedMail($order));
        }

        if (App::environment('production')) {
            Mail::to(config('contacts.email.link', 'info@barocco.by'))
                ->send(new OrderCreatedMail($order));
        }
    }
}
