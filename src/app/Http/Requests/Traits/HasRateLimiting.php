<?php

namespace App\Http\Requests\Traits;

use Drandin\DeclensionNouns\Facades\DeclensionNoun;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * @mixin \Illuminate\Foundation\Http\FormRequest
 */
trait HasRateLimiting
{
    /**
     * Apply rate limiting to the request
     */
    public function applyRateLimit(int $maxAttempts = 5, int $decaySeconds = 60): void
    {
        $key = class_basename($this) . ':' . $this->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'rate_limit' => $this->getRateLimitMessage(RateLimiter::availableIn($key)),
            ])->status(429);
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Get the rate limit error message.
     */
    public function getRateLimitMessage(int $remainingSeconds): string
    {
        return 'Слишком много попыток. Попробуйте через ' . DeclensionNoun::make($remainingSeconds, 'секунда');
    }
}
