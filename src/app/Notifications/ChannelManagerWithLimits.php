<?php

namespace App\Notifications;

use App\Events\Notifications\NotificationSkipped;
use Illuminate\Notifications\ChannelManager;

class ChannelManagerWithLimits extends ChannelManager
{
    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param  \Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function send($notifiables, $notification)
    {
        if ($this->checkAvailability($notification)) {
            parent::send($notifiables, $notification);
        } else {
            event(new NotificationSkipped($notifiables, $notification));
        }
    }

    /**
     * Check availability and limits for channel.
     */
    private function checkAvailability(mixed $notification): bool
    {
        if (method_exists($notification, 'checkAvailability')) {
            return $notification->checkAvailability();
        }

        return true;
    }
}
