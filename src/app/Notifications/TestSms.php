<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\SmsTrafficMessage;

class TestSms extends AbstractSmsTraffic
{
    /**
     *
     */
    public function toSmsTraffic($notifiable)
    {
        return (new SmsTrafficMessage)->content($this->getContent());
    }

    /**
     *
     */
    public function getContent(): string
    {
        return 'Test new message content';
    }
}
