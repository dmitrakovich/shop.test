<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SmsTrafficMessage;

abstract class AbstractSmsTraffic extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['smstraffic'];
    }

    /**
     * Get the SmsTraffic / SMS representation of the notification.
     *
     * @param mixed $notifiable
     * @return SmsTrafficMessage|string
     */
    abstract public function toSmsTraffic($notifiable);

    /**
     * Content for sms message
     */
    abstract public function getContent(): string;

    /**
     * Route for sms message
     */
    public function getRoute(): ?string
    {
        return config('smstraffic.route');
    }
}
