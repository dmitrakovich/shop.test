<?php

namespace App\Notifications;

use App\Models\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Facades\SmsTraffic;
use Illuminate\Notifications\Messages\SmsTrafficMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

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
     */
    public function toSmsTraffic(mixed $notifiable): SmsTrafficMessage|string
    {
        SmsTraffic::setDefaultOption('link_in_text', 1);

        return (new SmsTrafficMessage())->content($this->getContent());
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

    /**
     * Check availability and limits.
     */
    public function checkAvailability(): bool
    {
        if (Config::findCacheable('sms')['enabled'] === 'off') {
            return false;
        }

        $limits = [
            'sms-warning-100/1h' => [100, 3600, '⚠️ Внимание! Отправлено более 100 сообщений за час!', false],
            'sms-warning-1000/1d' => [1000, 86400, '⚠️ Внимание! Отправлено более 1000 сообщений за сутки!', false],
            'sms-stop-100/10m' => [100, 600, '⛔️ Отправка сообщений остановлена! Превышен лимит 100 сообщений за 10 минут!', true],
            'sms-stop-500/1h' => [500, 3600, '⛔️ Отправка сообщений остановлена! Превышен лимит 500 сообщений за час!', true],
        ];

        foreach ($limits as $key => [$maxAttempts, $decaySeconds, $message, $stop]) {
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $isMsgSentCacheKey = "$key-sent";
                if (Cache::missing($isMsgSentCacheKey)) {
                    Log::channel('telegram')->warning($message);
                }
                Cache::put($isMsgSentCacheKey, 1, RateLimiter::availableIn($key));

                if ($stop) {
                    return false;
                }
            }

            RateLimiter::hit($key, $decaySeconds);
        }

        return true;
    }
}
