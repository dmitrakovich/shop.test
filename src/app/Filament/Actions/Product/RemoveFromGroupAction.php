<?php

namespace App\Filament\Actions\Product;

use App\Models\Product;
use App\Services\Product\ProductGroupService;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class RemoveFromGroupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'removeFromGroup';
    }

    public function getLabel(): string|Htmlable|null
    {
        return 'Удалить товар из группы товаров';
    }

    /**
     * @return string | array<string> | null
     */
    public function getColor(): string|array|null
    {
        return 'danger';
    }

    public function isHidden(): bool
    {
        return !$this->getProduct()->product_group_id;
    }

    public function getActionFunction(): ?\Closure
    {
        return function (): void {
            $product = $this->getProduct();
            app(ProductGroupService::class)->removeFromProductGroup(
                $product->id,
                $product->product_group_id
            );
        };
    }

    public function getSuccessNotificationTitle(): ?string
    {
        return 'Товар удален из группы товаров!';
    }

    private function getProduct(): Product
    {
        return $this->getLivewire()->getOwnerRecord();
    }
}
