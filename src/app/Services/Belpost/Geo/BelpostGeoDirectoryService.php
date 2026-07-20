<?php

namespace App\Services\Belpost\Geo;

use App\Libraries\Belpost\BelpostGeoDirectory;
use App\Models\Orders\Order;

/**
 * Extracts address fields from an {@see Order} and resolves them via {@see BelpostGeoDirectory}.
 */
class BelpostGeoDirectoryService
{
    public function __construct(
        private readonly BelpostGeoDirectory $geoDirectory,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolveForOrder(Order $order): array
    {
        $hints = $this->extractAddressHints($order);

        return $this->geoDirectory->resolve($hints, "Order #{$order->id}");
    }

    /**
     * Prefer the order shipping fields; use structured `user.lastAddress` only after admin approval.
     *
     * @return array{
     *     postcode: ?string,
     *     city: ?string,
     *     street: ?string,
     *     building: ?string,
     *     housing: ?string,
     *     apartment: ?string,
     *     region: ?string,
     *     district: ?string,
     * }
     */
    private function extractAddressHints(Order $order): array
    {
        $order->loadMissing('user.lastAddress');
        $address = $order->user?->lastAddress;
        $useStructuredAddress = $address !== null && $address->approve;

        $postcode = $this->geoDirectory->normalizePostcode(
            $order->zip ?: ($useStructuredAddress ? $address->zip : null),
        );
        $city = $this->geoDirectory->normalizeCity(
            $order->city ?: ($useStructuredAddress ? $address->city : null),
        );
        $street = $this->geoDirectory->normalizeStreetName(
            ($useStructuredAddress ? $address->street : null)
            ?? $this->geoDirectory->parseStreetFromText($order->user_addr)
            ?? $this->geoDirectory->parseStreetFromText($address?->address)
        );

        return [
            'postcode' => $postcode,
            'city' => $city,
            'street' => $street,
            'building' => $this->geoDirectory->normalizeBuilding(
                $useStructuredAddress ? $address->house : null,
                $order->user_addr,
            ),
            'housing' => $this->geoDirectory->normalizeHousing($useStructuredAddress ? $address->corpus : null),
            'apartment' => $this->geoDirectory->normalizeApartment(
                $useStructuredAddress ? $address->room : null,
                $order->user_addr,
            ),
            'region' => $this->geoDirectory->normalizeRegion($order->region ?: ($useStructuredAddress ? $address->region : null)),
            'district' => $this->geoDirectory->normalizeDistrict($useStructuredAddress ? $address->district : null),
        ];
    }
}
