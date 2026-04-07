<?php

namespace App\Http\Middleware;

use App\Enums\Consent\ConsentFormEnum;
use App\Facades\Device as DeviceFacade;
use App\Models\User\DeviceConsent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PersistDeviceConsentHeaders
{
    private const string HEADER_COOKIE_ANALYTICS = 'X-Cookie-Analytics-Enabled';

    private const string HEADER_COOKIE_MARKETING = 'X-Cookie-Marketing-Enabled';

    private const string HEADER_PERSONAL_DATA_AT = 'X-Personal-Data-Consent-Recorded-At';

    private const string HEADER_PERSONAL_DATA = 'X-Personal-Data-Consent';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $consentForm): Response
    {
        $form = ConsentFormEnum::tryFrom((int)$consentForm);
        abort_if($form === null, Response::HTTP_INTERNAL_SERVER_ERROR);

        $updates = $this->collectUpdates($request);

        if ($updates === []) {
            return $next($request);
        }

        $updates['consent_request_source'] = $form;
        $updates['device_id'] = DeviceFacade::id();
        $updates['user_id'] = $request->user()?->id;

        DeviceConsent::query()->create($updates);

        return $next($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function collectUpdates(Request $request): array
    {
        $updates = [];

        $analytics = $this->parseBoolHeader($request, self::HEADER_COOKIE_ANALYTICS);
        if ($analytics !== null) {
            $updates['cookie_analytics_enabled'] = $analytics;
        }

        $marketing = $this->parseBoolHeader($request, self::HEADER_COOKIE_MARKETING);
        if ($marketing !== null) {
            $updates['cookie_marketing_enabled'] = $marketing;
        }

        if ($request->hasHeader(self::HEADER_PERSONAL_DATA_AT)) {
            $raw = $request->header(self::HEADER_PERSONAL_DATA_AT);
            if ($raw !== null && $raw !== '') {
                try {
                    $updates['personal_data_consent_recorded_at'] = Carbon::parse($raw);
                } catch (\Throwable) {
                    // ignore invalid datetime
                }
            }
        }

        $pdConsent = $this->parseBoolHeader($request, self::HEADER_PERSONAL_DATA);
        if ($pdConsent !== null) {
            $updates['personal_data_consent'] = $pdConsent;
        }

        $fio = $this->resolveFullName($request);
        if ($fio !== null) {
            $updates['fio'] = $fio;
        }

        return $updates;
    }

    private function resolveFullName(Request $request): ?string
    {
        $fullNameFromInput = $this->normalizePart($request->input('user_name'));
        if ($fullNameFromInput !== null) {
            return $fullNameFromInput;
        }

        $parts = [
            $this->normalizePart($request->input('last_name')),
            $this->normalizePart($request->input('first_name')),
            $this->normalizePart($request->input('patronymic_name')),
        ];

        $parts = array_values(array_filter($parts, fn (?string $part): bool => $part !== null));

        if ($parts === []) {
            return null;
        }

        return implode(' ', $parts);
    }

    private function normalizePart(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function parseBoolHeader(Request $request, string $name): ?bool
    {
        if (!$request->hasHeader($name)) {
            return null;
        }

        $value = $request->header($name);

        return match ($value) {
            'true' => true,
            'false' => false,
            default => null,
        };
    }
}
