<?php

namespace App\Filament\Actions\Product;

use App\Models\Product;
use App\Services\Product\ProductGroupService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class AddToGroupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'addToGroup';
    }

    public function getLabel(): string|Htmlable|null
    {
        return 'Добавить в группу товаров';
    }

    public function isHidden(): bool
    {
        return (bool)$this->getProduct()->product_group_id;
    }

    public function getModalSubmitActionLabel(): string
    {
        return 'Добавить';
    }

    public function getSchema(Schema $schema): ?Schema
    {
        return $schema->components([
            Select::make('product_id')
                ->label('ID товара')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => Product::query()
                    ->where('id', 'like', "{$search}%")
                    ->orderByDesc('id')
                    ->limit(50)
                    ->pluck('id', 'id')
                    ->all())
                ->getOptionLabelUsing(fn ($value): ?string => $value)
                ->required(),
        ]);
    }

    public function getActionFunction(): ?\Closure
    {
        return function (array $data): void {
            app(ProductGroupService::class)->addToProductGroup(
                $this->getProduct()->id,
                $data['product_id']
            );
        };
    }

    public function getSuccessNotificationTitle(): ?string
    {
        return 'Товар добавлен в группу товаров!';
    }

    private function getProduct(): Product
    {
        return $this->getLivewire()->getOwnerRecord();
    }
}
