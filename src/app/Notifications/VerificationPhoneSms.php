<?php

namespace App\Notifications;

class VerificationPhoneSms extends AbstractSmsTraffic
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private string $otp)
    {
    }

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        return 'Ваш код авторизации ' . $this->otp;
    }
}
