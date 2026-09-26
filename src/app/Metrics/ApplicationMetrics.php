<?php

namespace App\Metrics;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use Sentry\Unit;

/**
 * Application metrics sent to Sentry. Names stay stable so Explore charts keep their history.
 */
class ApplicationMetrics
{
    public function jobDuration(string $job, float $milliseconds): void
    {
        $this->distribution('job.duration', $milliseconds, ['job' => $job], Unit::millisecond());
    }

    public function oneCRows(int $count): void
    {
        $this->gauge('availability.onec_rows', $count);
    }

    public function productsUnpublished(int $count): void
    {
        $this->count('availability.products_unpublished', $count);
    }

    public function productsPublished(int $count): void
    {
        $this->count('availability.products_published', $count);
    }

    public function sizesAdded(int $count): void
    {
        $this->count('availability.sizes_added', $count);
    }

    public function sizesRemoved(int $count): void
    {
        $this->count('availability.sizes_removed', $count);
    }

    public function feedGenerated(
        string $feed,
        string $format,
        string $currency,
        int $sizeBytes,
        int $durationMs,
        float $memoryPeakMb,
    ): void {
        $attributes = [
            'feed' => $feed,
            'format' => $format,
            'currency' => $currency,
        ];

        $this->distribution('feed.duration', $durationMs, $attributes, Unit::millisecond());
        $this->gauge('feed.size_bytes', $sizeBytes, $attributes, Unit::byte());
        $this->gauge('feed.memory_peak', $memoryPeakMb, $attributes, Unit::megabyte());
    }

    public function paymentStatus(?OnlinePaymentStatusEnum $status, ?OnlinePaymentMethodEnum $method): void
    {
        if ($status === null || $method === null || $status === OnlinePaymentStatusEnum::PENDING) {
            return;
        }

        $this->count('payment.status', 1, [
            'status' => $status->name,
            'method' => $method->name,
        ]);
    }

    /**
     * @param  array<string, int|float|string|bool|null>  $attributes
     */
    protected function count(string $name, int|float $value, array $attributes = []): void
    {
        \Sentry\traceMetrics()->count($name, $value, $attributes);
    }

    /**
     * @param  array<string, int|float|string|bool|null>  $attributes
     */
    protected function gauge(string $name, int|float $value, array $attributes = [], ?Unit $unit = null): void
    {
        \Sentry\traceMetrics()->gauge($name, $value, $attributes, $unit);
    }

    /**
     * @param  array<string, int|float|string|bool|null>  $attributes
     */
    protected function distribution(string $name, int|float $value, array $attributes = [], ?Unit $unit = null): void
    {
        \Sentry\traceMetrics()->distribution($name, $value, $attributes, $unit);
    }
}
