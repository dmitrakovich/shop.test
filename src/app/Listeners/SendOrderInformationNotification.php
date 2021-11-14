<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\OrderCreated as OrderCreatedMail;

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
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
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
