<?php

namespace App\Traits;

use App\Facades\Sale;
use App\Models\Data\SaleData;

trait ProductSales
{
    /**
     * Sales that apply to the product
     *
     * @var array{list: array<string, SaleData>, final_price: float}|null
     */
    private ?array $sales = null;

    /**
     * Get current sales
     *
     * @return array{list: array<string, SaleData>, final_price: float}
     */
    private function sales(): array
    {
        $this->applySales();

        return $this->sales;
    }

    /**
     * Apply sales to the product
     */
    public function applySales(): void
    {
        if (is_null($this->sales)) {
            Sale::applyForProduct($this);
        }
    }

    /**
     * Get final price after apply other sales
     *
     * @return float
     */
    public function getFinalPrice()
    {
        return $this->sales()['final_price'];
    }

    /**
     * Set product sales
     *
     * @param  array<string, SaleData>  $sales
     */
    public function setSales(array $sales, float $finalPrice): void
    {
        $this->sales = [
            'list' => $sales,
            'final_price' => $finalPrice,
        ];
    }

    /**
     * Check product's sales
     */
    public function hasSales(): bool
    {
        return !empty($this->sales()['list']);
    }

    /**
     * Get current product's sales
     *
     * @return array<string, SaleData>
     */
    public function getSales(): array
    {
        return $this->sales()['list'];
    }

    /**
     * Get specific product's sale
     */
    public function getSale(string $saleKey): ?SaleData
    {
        return $this->getSales()[$saleKey] ?? null;
    }

    /**
     * Calculate sale percentage
     */
    public function getSalePercentage(): int
    {
        return (int)ceil((1 - ($this->getFinalPrice() / $this->getFinalOldPrice())) * 100);
    }
}
