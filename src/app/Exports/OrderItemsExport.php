<?php

namespace App\Exports;

use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * @implements WithMapping<OrderItem>
 */
class OrderItemsExport implements FromQuery, WithColumnWidths, WithEvents, WithHeadings, WithMapping
{
    /**
     * Order ids in row order, to turn order cells into links.
     *
     * @var list<int>
     */
    private array $orderIds = [];

    /**
     * @param  Builder<OrderItem>  $query  filtered and sorted table query
     */
    public function __construct(private readonly Builder $query) {}

    /**
     * @return Builder<OrderItem>
     */
    public function query(): Builder
    {
        return $this->query->setEagerLoads([])->with([
            'order:id,created_at',
            'product:id,sku,brand_id',
            'product.brand:id,name',
            'size:id,name',
            'statusLog:id,order_item_id,stock_id,sended_at,completed_at',
            'statusLog.stock:id,internal_name',
        ]);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'ID позиции',
            'Код товара',
            'Товар',
            'Размер',
            'Заказ',
            'Статус',
            'Склад',
            'Дата заказа',
            'Дата отправки',
            'Дата выкупа',
        ];
    }

    /**
     * @param  OrderItem  $orderItem
     * @return list<int|string|null>
     */
    public function map($orderItem): array
    {
        $this->orderIds[] = $orderItem->order_id;

        return [
            $orderItem->id,
            $orderItem->product_id,
            $orderItem->product->exists
                ? trim("{$orderItem->product->brand?->name} {$orderItem->product->sku}")
                : 'Товар удалён',
            $orderItem->size?->name,
            $orderItem->order_id,
            $orderItem->status->getLabel(),
            $orderItem->statusLog?->stock?->internal_name,
            $orderItem->order?->created_at?->format('d.m.Y H:i'),
            $orderItem->statusLog?->sended_at?->format('d.m.Y H:i'),
            $orderItem->statusLog?->completed_at?->format('d.m.Y H:i'),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 12,
            'C' => 36,
            'D' => 10,
            'E' => 10,
            'F' => 22,
            'G' => 24,
            'H' => 17,
            'I' => 17,
            'J' => 17,
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');
                $sheet->getStyle('1')->getFont()->setBold(true);

                foreach ($this->orderIds as $index => $orderId) {
                    $sheet->getCell('E' . ($index + 2))->getHyperlink()->setUrl(url("old-admin/orders/{$orderId}/edit"));
                }
            },
        ];
    }
}
