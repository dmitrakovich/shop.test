<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SmsTrafficMessage;

class TestSms extends Notification
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
     * @return SmsTrafficMessage
     */
    public function toSmsTraffic($notifiable)
    {
        return (new SmsTrafficMessage)
                    ->content('Test message content');
    }
}
