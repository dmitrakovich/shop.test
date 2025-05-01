<?php

namespace App\Http\Resources\Info;

use App\Facades\Currency;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class InstallmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!$this->availableInstallment()) {
            return [];
        }

        return $this->getParts();
    }

    private function getParts(): array
    {
        return $this->getPrice() >= $this->getMinPriceFor3Parts()
            ? $this->get3Parts($this->getPrice())
            : $this->get2Parts($this->getPrice());
    }

    private function get2Parts(float $price): array
    {
        return [
            Currency::round($price - ($price * 0.5)),
            Currency::round($price * 0.5),
        ];
    }

    private function get3Parts(float $price): array
    {
        return [
            Currency::round($price - ($price * 0.6)),
            Currency::round($price * 0.3),
            Currency::round($price * 0.3),
        ];
    }

    private function getMinPriceFor3Parts(): float
    {
        return Config::findCacheable('installment')['min_price_3_parts'] ?? 150;
    }
}
