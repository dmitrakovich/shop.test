<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SmsTrafficMessage;
use Illuminate\Notifications\Notification;

abstract class AbstractSmsTraffic extends Notification
{
    use Queueable;

    /**
     * Mailing identificator if exists
     */
    public ?int $mailingId = null;

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
     * @param  mixed  $notifiable
     * @return SmsTrafficMessage|string
     */
    public function toSmsTraffic($notifiable)
    {
        return (new SmsTrafficMessage)->content($this->getContent());
    }

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

    /**
     * Get the id of the mailing list to which the notification belongs
     */
    public function getMailingId(): ?int
    {
        return $this->mailingId;
    }
}
