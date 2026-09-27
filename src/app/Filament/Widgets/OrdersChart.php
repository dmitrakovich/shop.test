<?php

namespace App\Filament\Widgets;

use App\Models\Orders\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    private const int DAYS = 45;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Заказы';

    protected ?string $description = 'По дате создания, последние 45 дней';

    protected ?string $maxHeight = '500px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array{datasets: list<array{label: string, data: list<int>}>, labels: list<string>}
     */
    protected function getData(): array
    {
        $start = today()->subDays(self::DAYS - 1);

        /** @var array<string, int> $counts */
        $counts = Order::query()
            ->where('created_at', '>=', $start)
            ->toBase()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day')
            ->mapWithKeys(fn (mixed $total, mixed $day): array => [(string)$day => (int)$total])
            ->all();

        $labels = [];
        $data = [];

        for ($day = $start->copy(); $day->lte(today()); $day->addDay()) {
            $labels[] = $day->format('d.m');
            $data[] = $counts[$day->toDateString()] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Заказы',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
