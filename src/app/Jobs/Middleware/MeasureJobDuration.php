<?php

namespace App\Jobs\Middleware;

use App\Jobs\AbstractJob;
use App\Metrics\ApplicationMetrics;
use Closure;

class MeasureJobDuration
{
    public function __construct(private ApplicationMetrics $metrics) {}

    public function handle(AbstractJob $job, Closure $next): mixed
    {
        $startedAt = microtime(true);

        try {
            return $next($job);
        } finally {
            $this->metrics->jobDuration(class_basename($job), (microtime(true) - $startedAt) * 1000);
        }
    }
}
