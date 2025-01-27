<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Expenses on purchases';

    protected static string $color = 'danger';

    protected static ?int $sort = 0;

    protected function getData(): array
    {
        $expenses = \Flowframe\Trend\Trend::model(Purchase::class)
            ->between(now()->startOfYear(), now()->endOfYear())
            ->perMonth()
            ->sum('total_cost');

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $expenses->map(fn (\Flowframe\Trend\TrendValue $trend) => $trend->aggregate / 100),
                ],
            ],
            'labels' => $expenses->map(fn (\Flowframe\Trend\TrendValue $trend) => now()->parse($trend->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
