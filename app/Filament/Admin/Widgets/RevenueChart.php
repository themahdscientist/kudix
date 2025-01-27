<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Sale;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue from sales';

    protected static string $color = 'success';

    protected static ?int $sort = -1;

    protected function getData(): array
    {
        $revenue = \Flowframe\Trend\Trend::model(Sale::class)
            ->between(now()->startOfYear(), now()->endOfYear())
            ->perMonth()
            ->sum('total_price');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue->map(fn (\Flowframe\Trend\TrendValue $trend) => $trend->aggregate / 100),
                ],
            ],
            'labels' => $revenue->map(fn (\Flowframe\Trend\TrendValue $trend) => now()->parse($trend->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
