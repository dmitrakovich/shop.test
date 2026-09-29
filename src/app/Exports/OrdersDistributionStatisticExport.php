<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<array{
 *     __key: string,
 *     instance_name: string,
 *     distribution_count: int,
 *     distribution_percentage: float,
 *     created_manually: int,
 *     accepted_manually: int,
 *     total_count: int
 * }>
 */
class OrdersDistributionStatisticExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, array{
     *     __key: string,
     *     instance_name: string,
     *     distribution_count: int,
     *     distribution_percentage: float,
     *     created_manually: int,
     *     accepted_manually: int,
     *     total_count: int
     * }>  $rows
     */
    public function __construct(private readonly Collection $rows) {}

    /**
     * @return Collection<int, array{
     *     __key: string,
     *     instance_name: string,
     *     distribution_count: int,
     *     distribution_percentage: float,
     *     created_manually: int,
     *     accepted_manually: int,
     *     total_count: int
     * }>
     */
    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Менеджер',
            'Заказов распределено',
            '% от всех распределенных',
            'Создано вручную',
            'Принято вручную',
            'Всего заказов (по дате создания)',
        ];
    }

    /**
     * @param  array{
     *     __key: string,
     *     instance_name: string,
     *     distribution_count: int,
     *     distribution_percentage: float,
     *     created_manually: int,
     *     accepted_manually: int,
     *     total_count: int
     * }  $row
     * @return list<float|int|string>
     */
    public function map($row): array
    {
        return [
            $row['instance_name'],
            $row['distribution_count'],
            $row['distribution_percentage'],
            $row['created_manually'],
            $row['accepted_manually'],
            $row['total_count'],
        ];
    }
}
