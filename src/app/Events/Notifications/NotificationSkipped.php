<?php

namespace App\Events\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NotificationSkipped
{
    use Queueable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $notifiable The notifiable entity who received the notification.
     * @param  Notification  $notification The notification instance.
     */
    public function __construct(
        public mixed $notifiable,
        public Notification $notification
    ) {
    }
}
