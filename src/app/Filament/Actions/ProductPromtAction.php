<?php

namespace App\Filament\Actions;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class ProductPromtAction extends Action
{
    public static function make(?string $name = 'productPromt'): static
    {
        return parent::make($name)
            ->label('Составить промт')
            ->icon(Heroicon::OutlinedPencil)
            ->action(function (CreateRecord|EditRecord $livewire) {
                /** @var Product $product */
                $product = $livewire->record ?? new Product($livewire->data);

                $properties = collect();
                if ($product->heel_txt) {
                    $properties->push('высота каблука/подошвы ' . $product->heel_txt);
                }
                if ($product->fabric_outsole_txt) {
                    $properties->push('материал подошвы ' . $product->fabric_outsole_txt);
                }
                if ($product->fabric_insole_txt) {
                    $properties->push('материал стельки ' . $product->fabric_insole_txt);
                }
                if ($product->fabric_inner_txt) {
                    $properties->push('материал внутри ' . $product->fabric_inner_txt);
                }
                if ($product->fabric_top_txt) {
                    $properties->push('материал верха ' . $product->fabric_top_txt);
                }

                $livewire->dispatch('copy-product-promt', <<<TEXT
                    Составь структурированное описание товара для интернет-магазина.
                    План описания:
                    1. Вводное первое предложение: категория товара, цвет и из какого материала сделан.
                    2. Ключевая особенность товара и ее преимущество для покупателя. Объем 1-3 предложения.
                    3. Описание товара используя список характеристик. Объем 3-7 предложений.
                    4. Для каких ситуаций подойдет исходя из стиля. Объем 2-5 предложений.
                    5. С какой одеждой сочетается. Объем 2-4 предложения.
                    Исходные данные о товаре:
                    1. Наименование - {$product->category?->title}
                    2. Материал - {$product->fabrics->implode('name', ', ')}
                    3. Цвет - {$product->colors->implode('name', ', ')}
                    4. Ключевая особенность - {$product->key_features}
                    5. Список характеристик: {$properties->implode(', ')};
                    TEXT
                );
            })
            ->extraAttributes([
                'x-on:copy-product-promt.window' => self::getLivewareJs(),
            ]);
    }

    private static function getLivewareJs(): string
    {
        return <<<'JS'
        window.navigator.clipboard.writeText($event.detail[0])
            .then(() =>  new FilamentNotification()
                .title('Описание скопировано в буфер обмена')
                .icon('heroicon-o-clipboard-document-list')
                .iconColor('primary')
                .send());
        JS;
    }
}
