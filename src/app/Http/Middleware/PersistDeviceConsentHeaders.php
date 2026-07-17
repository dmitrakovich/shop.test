<?php

namespace App\Http\Middleware;

use App\Enums\Consent\ConsentFormEnum;
use App\Facades\Device as DeviceFacade;
use App\Models\User\DeviceConsent;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PersistDeviceConsentHeaders
{
    private const string HEADER_COOKIE_ANALYTICS = 'X-Cookie-Analytics-Enabled';

    private const string HEADER_COOKIE_MARKETING = 'X-Cookie-Marketing-Enabled';

    private const string HEADER_PERSONAL_DATA_AT = 'X-Personal-Data-Consent-Recorded-At';

    private const string HEADER_PERSONAL_DATA = 'X-Personal-Data-Consent';

    private const array CONSENT_FIELDS = [
        'cookie_analytics_enabled',
        'cookie_marketing_enabled',
        'personal_data_consent',
    ];

    /**
     * Persist consent side-effects without blocking the main request.
     * Consent must never produce a 500 for checkout/login/feedback POSTs.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $consentForm): Response
    {
        try {
            $this->persistConsent($request, $consentForm);
        } catch (Throwable $e) {
            report($e);
        }

        return $next($request);
    }

    private function persistConsent(Request $request, string $consentForm): void
    {
        $form = ConsentFormEnum::tryFrom((int)$consentForm) ?? ConsentFormEnum::Unknown;

        $updates = $this->collectUpdates($request);

        if ($updates === []) {
            return;
        }

        $user = $this->resolveUser($request, $updates['phone'] ?? null);

        $updates['consent_request_source'] = $form;
        $updates['device_id'] = DeviceFacade::id();
        $updates['user_id'] = $user?->id;

        if (!isset($updates['fio'])) {
            $fio = $this->normalizePart($user?->getFullName());
            if ($fio !== null) {
                $updates['fio'] = $fio;
            }
        }

        if (!$this->hasConsentChanges($updates)) {
            return;
        }

        DeviceConsent::query()->create($updates);
    }

    /**
     * Resolve the user from the authenticated request or by phone from the body.
     * Login runs this middleware before auth, so registered users are looked up by phone.
     */
    private function resolveUser(Request $request, mixed $phone): ?User
    {
        $authenticated = $request->user();
        if ($authenticated instanceof User) {
            return $authenticated;
        }

        if (!is_string($phone) || $phone === '') {
            return null;
        }

        try {
            return User::getByPhone(Phone::fromRawString($phone));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Skip write when:
     * - this device already has the same consent flags, or
     * - this device has no consent row yet, but the user (resolved by phone)
     *   already has the same consent flags.
     *
     * @param  array<string, mixed>  $updates
     */
    private function hasConsentChanges(array $updates): bool
    {
        $byDevice = isset($updates['device_id'])
            ? DeviceConsent::query()
                ->where('device_id', $updates['device_id'])
                ->latest('id')
                ->first()
            : null;

        if ($byDevice !== null) {
            return $this->consentFieldsDiffer($updates, $byDevice);
        }

        if (!isset($updates['user_id'])) {
            return true;
        }

        $byUser = DeviceConsent::query()
            ->where('user_id', $updates['user_id'])
            ->latest('id')
            ->first();

        if ($byUser === null) {
            return true;
        }

        return $this->consentFieldsDiffer($updates, $byUser);
    }

    /**
     * @param  array<string, mixed>  $updates
     */
    private function consentFieldsDiffer(array $updates, DeviceConsent $existing): bool
    {
        foreach (self::CONSENT_FIELDS as $field) {
            if (!array_key_exists($field, $updates)) {
                continue;
            }

            if ($existing->{$field} !== $updates[$field]) {
                return true;
            }
        }

        return false;
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
                } catch (Throwable) {
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

        $phone = $this->normalizePart($request->input('phone'));
        if ($phone !== null) {
            $updates['phone'] = $phone;
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
