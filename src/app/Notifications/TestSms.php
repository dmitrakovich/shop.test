<?php

namespace App\Notifications;

class TestSms extends AbstractSmsTraffic
{
    public function getContent(): string
    {
        return 'Test new message content';
    }
}
