<?php

namespace App\Exports;

use App\Enums\Analytics\AnalyticReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<array<string, mixed>>
 */
class AnalyticsReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int|string, array<string, mixed>>  $rows
     */
    public function __construct(
        private readonly AnalyticReport $report,
        private readonly Collection $rows,
    ) {}

    /**
     * @return Collection<int|string, array<string, mixed>>
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
        $headings = [
            $this->report->dimensionLabel(),
        ];

        if ($this->report->showsUtmDetails()) {
            $headings[] = 'Канал';
            $headings[] = 'Компания';
        }

        return [
            ...$headings,
            'Все',
            'Принят',
            'В работе',
            'Выкуплен',
            'Отменен',
            'Возврат',
            'Сумма выкупленных',
            'Процент выкупа',
            'Сумма потерянных',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<float|int|string|null>
     */
    public function map($row): array
    {
        $values = [
            $row['instance_name'],
        ];

        if ($this->report->showsUtmDetails()) {
            $values[] = $row['channel_name'] ?? null;
            $values[] = $row['company_name'] ?? null;
        }

        return [
            ...$values,
            $row['total_count'],
            $row['accepted_count'],
            $row['in_progress_count'],
            $row['purchased_count'],
            $row['canceled_count'],
            $row['returned_count'],
            $row['total_purchased_price'],
            $row['purchase_percentage'],
            $row['total_lost_price'],
        ];
    }
}
