<?php

namespace App\Traits;

use App\Facades\Sale;
use App\Models\Data\SaleData;

trait ProductSales
{
    /**
     * Sales that apply to the product
     */
    private ?array $sales = null;

    /**
     * Get current sales
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
     * Get fianl price after apply other sales
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
     * @param  array{list: array[], final_price: float}  $sales
     */
    public function setSales(array $sales): void
    {
        $this->sales = $sales;
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
     *
     * @return int
     */
    public function getSalePercentage(): int
    {
        return ceil((1 - ($this->getFinalPrice() / $this->getFinalOldPrice())) * 100);
    }
}
