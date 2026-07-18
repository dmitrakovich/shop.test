<?php

namespace App\Enums;

enum Queue: string
{
    case High = 'high';
    case Default = 'default';
    case Low = 'low';
    case Pixel = 'pixel';
    case Media = 'media';
    case OneC = 'one_c';

    public function redisWaitKey(): string
    {
        return 'redis:' . $this->value;
    }

    public function horizonWaitSeconds(): int
    {
        return match ($this) {
            self::Media => 120,
            default => 60,
        };
    }

    /**
     * Queues for Horizon "laravel-worker" supervisor (priority order).
     *
     * @return list<string>
     */
    public static function horizonWorkerQueues(): array
    {
        return [
            self::High->value,
            self::Default->value,
            self::OneC->value,
            self::Low->value,
        ];
    }

    /**
     * Single Horizon supervisor (e.g. local) — all queues including media.
     *
     * @return list<string>
     */
    public static function horizonAllQueuesOrdered(): array
    {
        return [
            self::High->value,
            self::Media->value,
            self::Default->value,
            self::Pixel->value,
            self::OneC->value,
            self::Low->value,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function horizonRedisWaitThresholds(): array
    {
        $waits = [];
        foreach (self::cases() as $queue) {
            $waits[$queue->redisWaitKey()] = $queue->horizonWaitSeconds();
        }

        // Media is consumed via the redis-long connection in Horizon.
        $waits['redis-long:' . self::Media->value] = self::Media->horizonWaitSeconds();

        return $waits;
    }
}
