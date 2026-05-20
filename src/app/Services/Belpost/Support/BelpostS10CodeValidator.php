<?php

namespace App\Services\Belpost\Support;

use App\Services\Departures\BelpostLabelService;

/**
 * Validates Belarus Post S10 identifiers (e.g. PE123456785BY).
 */
class BelpostS10CodeValidator
{
    public function __construct(
        private readonly BelpostLabelService $labelService,
    ) {}

    public function normalize(mixed $code): ?string
    {
        if (!is_string($code) && !is_int($code) && !is_float($code)) {
            return null;
        }

        $normalized = strtoupper(preg_replace('/\s+/', '', (string)$code) ?? '');

        return $normalized !== '' ? $normalized : null;
    }

    public function isValid(?string $code): bool
    {
        $code = $this->normalize($code);
        if ($code === null || strlen($code) !== 13) {
            return false;
        }

        if (!preg_match('/^[A-Z]{2}(\d{8})(\d)BY$/', $code, $matches)) {
            return false;
        }

        try {
            return $this->labelService->calculateCheckSum($matches[1]) === (int)$matches[2];
        } catch (\Exception) {
            return false;
        }
    }

    public function extractSerialNumber(?string $code): ?int
    {
        $normalized = $this->normalize($code);
        if ($normalized === null || !preg_match('/^[A-Z]{2}(\d{8})\dBY$/', $normalized, $matches)) {
            return null;
        }

        return (int)$matches[1];
    }

    /**
     * @return string|null Normalized code when serial is within optional contract bounds
     */
    public function validateSerialRange(?string $code, ?int $min, ?int $max): ?string
    {
        $normalized = $this->normalize($code);
        if ($normalized === null) {
            return null;
        }

        $serial = $this->extractSerialNumber($normalized);
        if ($serial === null) {
            return null;
        }

        if ($min !== null && $serial < $min) {
            return null;
        }

        if ($max !== null && $serial > $max) {
            return null;
        }

        return $normalized;
    }

    public function seriesPrefix(?string $code): ?string
    {
        $normalized = $this->normalize($code);

        return $normalized !== null && strlen($normalized) >= 2
            ? substr($normalized, 0, 2)
            : null;
    }

    /**
     * Whether the series matches prefixes allocated for this merchant in Belpost API.
     *
     * @param  list<string>  $allowedPrefixes
     */
    public function isAllowedSeries(?string $code, array $allowedPrefixes): bool
    {
        $prefix = $this->seriesPrefix($code);
        if ($prefix === null || $allowedPrefixes === []) {
            return false;
        }

        $allowed = array_map(static fn (string $p): string => strtoupper(trim($p)), $allowedPrefixes);

        return in_array($prefix, $allowed, true);
    }

    /**
     * @return string|null Normalized S10 code when valid
     */
    public function validate(mixed $code): ?string
    {
        $normalized = $this->normalize($code);
        if ($normalized === null || !$this->isValid($normalized)) {
            return null;
        }

        return $normalized;
    }

    /**
     * @param  list<string>  $allowedPrefixes
     * @return string|null Normalized code when format/checksum/series are OK for API
     */
    public function validateForApi(mixed $code, array $allowedPrefixes): ?string
    {
        $normalized = $this->validate($code);
        if ($normalized === null) {
            return null;
        }

        if ($allowedPrefixes !== [] && !$this->isAllowedSeries($normalized, $allowedPrefixes)) {
            return null;
        }

        return $normalized;
    }
}
