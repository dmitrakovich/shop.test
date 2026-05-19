<?php

namespace App\Libraries\Belpost;

use App\Libraries\Belpost\Exceptions\BelpostApiException;

/**
 * Model-free Belpost geo-directory client: autocomplete → postcode lookup → street/building search.
 *
 * Normalizes BY addresses into the structure required by the recipient API.
 */
class BelpostGeoDirectory
{
    public function __construct(
        private readonly HttpClient $client,
    ) {}

    /**
     * Resolve address via Belpost geo directory.
     *
     * @param  array{
     *     postcode: ?string,
     *     city: ?string,
     *     street: ?string,
     *     building: ?string,
     *     housing: ?string,
     *     region: ?string,
     *     district: ?string,
     * }  $hints
     * @return array<string, mixed>
     */
    public function resolve(array $hints, string $notFoundContext = ''): array
    {
        $searchString = $this->formatSearchString($hints);
        $rows = [];

        // Try autocomplete first, then postcode-only, then city + street + building.
        if ($searchString !== '') {
            $rows = $this->lookupRows('/api/v1/postcodes/autocomplete', ['search' => $searchString]);
        }

        if ($rows === [] && $hints['postcode'] !== null) {
            $rows = $this->lookupRows('/api/v1/business/geo-directory/addresses', ['postcode' => $hints['postcode']]);
        }

        if ($rows === [] && $hints['city'] !== null && $hints['street'] !== null) {
            $query = [
                'city' => $hints['city'],
                'street' => $hints['street'],
                'limit' => 50,
            ];

            if ($hints['building'] !== null) {
                $query['building'] = $this->formatBuildingNumber($hints['building'], $hints['housing']);
            }

            $rows = $this->lookupRows('/api/v1/business/geo-directory/postcode', $query);
        }

        $match = $this->pickBestMatch($rows, $hints);

        if ($match === null) {
            $suffix = $searchString !== '' ? " Query: {$searchString}" : '';
            $prefix = $notFoundContext !== '' ? "{$notFoundContext}: " : '';

            throw new BelpostApiException(
                "{$prefix}address not found in Belpost directory.{$suffix}"
            );
        }

        return $this->mapToRecipientAddress($match, $hints);
    }

    /**
     * @param  array{
     *     postcode: ?string,
     *     city: ?string,
     *     street: ?string,
     *     building: ?string,
     *     housing: ?string,
     * }  $hints
     */
    public function formatSearchString(array $hints): string
    {
        $parts = [];

        if ($hints['postcode'] !== null) {
            $parts[] = $hints['postcode'];
        }

        if ($hints['city'] !== null) {
            $parts[] = 'город ' . $hints['city'];
        }

        if ($hints['street'] !== null) {
            $parts[] = 'улица ' . $hints['street'];
        }

        if ($hints['building'] !== null) {
            $parts[] = $this->formatBuildingNumber($hints['building'], $hints['housing']);
        }

        return $this->formatAddressString(implode(' ', $parts));
    }

    public function formatAddressString(string $address): string
    {
        $address = preg_replace('/\s+/', ' ', trim($address)) ?? '';
        $address = preg_replace('/\s*корпус\s*(\d+)\s*/iu', '/$1', $address) ?? $address;
        $address = preg_replace('/\s*корп\s*(\d+)\s*/iu', '/$1', $address) ?? $address;
        $address = preg_replace('/\s*кор\s*(\d+)\s*/iu', '/$1', $address) ?? $address;
        $address = preg_replace('/\s*к\s*(\d+)\s*/iu', '/$1', $address) ?? $address;

        return trim($address);
    }

    public function formatBuildingNumber(string $building, ?string $housing): string
    {
        $building = trim($building);

        if ($housing !== null && $housing !== '') {
            return $this->formatAddressString($building . '/' . $housing);
        }

        return $building;
    }

    public function normalizePostcode(mixed $zip): ?string
    {
        if ($zip === null || trim((string)$zip) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string)$zip);

        return strlen($digits) === 6 ? $digits : null;
    }

    public function normalizeCity(mixed $city): ?string
    {
        if ($city === null || trim((string)$city) === '') {
            return null;
        }

        $city = trim((string)$city);
        $city = preg_replace('/^(г\.?|город)\s+/iu', '', $city) ?? $city;

        return mb_substr(trim($city), 0, 25) ?: null;
    }

    public function normalizeStreetName(mixed $street): ?string
    {
        if ($street === null || trim((string)$street) === '') {
            return null;
        }

        $street = trim((string)$street);
        $street = preg_replace('/^(ул\.?|улица|пр\.?|просп\.?|проспект|пер\.?|переулок|б-р\.?|бульвар|пл\.?|площадь)\s+/iu', '', $street) ?? $street;

        return mb_substr(trim($street), 0, 50) ?: null;
    }

    public function normalizeBuilding(?string $house, ?string $userAddr): ?string
    {
        if ($house !== null && trim($house) !== '') {
            return trim($house);
        }

        if ($userAddr !== null && preg_match('/(?:д\.?|дом)\s*([0-9]+[A-Za-zА-Яа-я\-\/]*)/iu', $userAddr, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function normalizeHousing(?string $corpus): ?string
    {
        if ($corpus === null || trim($corpus) === '') {
            return null;
        }

        return trim($corpus);
    }

    public function normalizeRegion(mixed $region): ?string
    {
        if ($region === null || trim((string)$region) === '') {
            return null;
        }

        $region = trim((string)$region);
        $region = preg_replace('/\s+(область|обл\.?)$/iu', '', $region) ?? $region;

        return mb_substr($region, 0, 22) ?: null;
    }

    public function normalizeDistrict(mixed $district): ?string
    {
        if ($district === null || trim((string)$district) === '') {
            return null;
        }

        $district = trim((string)$district);
        $district = preg_replace('/\s+(район|р-н\.?)$/iu', '', $district) ?? $district;

        return mb_substr($district, 0, 18) ?: null;
    }

    public function parseStreetFromText(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        if (preg_match('/(?:ул\.?|улица|пр\.?|проспект|пер\.?|бульвар|б-р\.?|пл\.?)\s*[^,;]+/iu', $text, $matches)) {
            return $this->normalizeStreetName($matches[0]);
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array{
     *     postcode: ?string,
     *     city: ?string,
     *     street: ?string,
     *     building: ?string,
     *     housing: ?string,
     * }  $hints
     * @return array<string, mixed>|null
     */
    private function pickBestMatch(array $rows, array $hints): ?array
    {
        if ($rows === []) {
            return null;
        }

        if ($hints['building'] === null) {
            return $rows[0];
        }

        $target = $this->formatBuildingNumber($hints['building'], $hints['housing']);

        foreach ($rows as $row) {
            $building = (string)($this->pick($row, ['building', 'house', 'house_number']) ?? '');

            if ($building === '' && isset($row['buildings'])) {
                $building = $this->pickBuildingFromList((string)$row['buildings'], $hints['building']);
            }

            if ($building !== '' && strcasecmp($building, $target) === 0) {
                return $row;
            }
        }

        return $rows[0];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array{
     *     postcode: ?string,
     *     city: ?string,
     *     street: ?string,
     *     building: ?string,
     *     housing: ?string,
     * }  $hints
     * @return array<string, mixed>
     */
    private function mapToRecipientAddress(array $row, array $hints): array
    {
        $postcode = $this->normalizePostcode($this->pick($row, ['postcode', 'post_index', 'index', 'zip']));

        if ($postcode === null) {
            throw new BelpostApiException('Belpost geo directory result does not contain a valid postcode.');
        }

        $city = $this->normalizeCity($this->pick($row, ['city', 'locality', 'settlement']));
        $street = $this->normalizeStreetName($this->pick($row, ['street', 'street_name', 'streetName']));

        if ($city === null || $street === null) {
            throw new BelpostApiException('Belpost geo directory result is missing city or street.');
        }

        $building = $this->pick($row, ['building', 'house', 'house_number']);

        if (($building === null || $building === '') && isset($row['buildings'])) {
            $building = $this->pickBuildingFromList((string)$row['buildings'], $hints['building']);
        }

        $building = mb_substr((string)($building ?? '1'), 0, 8);
        $housing = $this->pick($row, ['housing', 'corpus', 'block']);
        $apartment = $this->pick($row, ['apartment', 'flat', 'room']);

        $payload = [
            'address_type' => 'address',
            'postcode' => $postcode,
            'country_code' => 'BY',
            'city' => $city,
            'city_type' => mb_substr((string)($this->pick($row, ['city_type', 'cityType']) ?? 'город'), 0, 30),
            'street' => $street,
            'street_type' => mb_substr((string)($this->pick($row, ['street_type', 'streetType']) ?? 'улица'), 0, 10),
            'building' => $building,
        ];

        $region = $this->normalizeRegion($this->pick($row, ['region', 'oblast', 'area']));
        if ($region !== null) {
            $payload['region'] = $region;
        }

        $district = $this->normalizeDistrict($this->pick($row, ['district', 'raion', 'area_district']));
        if ($district !== null) {
            $payload['district'] = $district;
        }

        if ($housing === null || $housing === '') {
            $housing = $hints['housing'];
        }

        $payload['housing'] = mb_substr((string)($housing ?? ''), 0, 2);

        if ($apartment !== null && $apartment !== '') {
            $apartment = preg_replace('/^(кв\.?|квартира)\s*/iu', '', trim((string)$apartment)) ?? trim((string)$apartment);
            $payload['apartment'] = mb_substr($apartment, 0, 5);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    private function lookupRows(string $path, array $query): array
    {
        try {
            return $this->unwrapRows($this->client->get($path, $query));
        } catch (BelpostApiException $exception) {
            if ($this->isRecoverableLookupError($exception)) {
                return [];
            }

            throw $exception;
        }
    }

    private function isRecoverableLookupError(BelpostApiException $exception): bool
    {
        if (in_array($exception->statusCode, [401, 403], true)) {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'invalid scope')
            || str_contains($message, 'scope(s) provided');
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, array<string, mixed>>
     */
    private function unwrapRows(array $response): array
    {
        foreach (['data', 'addresses', 'results', 'items'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $this->normalizeRowsList($response[$key]);
            }
        }

        return $this->normalizeRowsList($response);
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRowsList(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        if (!array_is_list($rows)) {
            return [$rows];
        }

        return array_values(array_filter($rows, is_array(...)));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private function pick(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    private function pickBuildingFromList(string $buildings, ?string $preferred): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $buildings))));

        if ($parts === []) {
            return '1';
        }

        if ($preferred !== null && $preferred !== '') {
            foreach ($parts as $part) {
                if (strcasecmp($part, $preferred) === 0) {
                    return $part;
                }
            }

            foreach ($parts as $part) {
                if (str_starts_with(strtolower($part), strtolower($preferred))) {
                    return $part;
                }
            }
        }

        return $parts[0];
    }
}
