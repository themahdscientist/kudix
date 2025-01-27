<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Supplier;
use Filament\Widgets\ChartWidget;

class SuppliersChart extends ChartWidget
{
    protected static ?string $heading = 'Suppliers';

    protected static string $color = 'danger';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $suppliers = \Flowframe\Trend\Trend::model(Supplier::class)
            ->between(now()->startOfYear(), now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Suppliers',
                    'data' => $suppliers->map(fn (\Flowframe\Trend\TrendValue $trend) => $trend->aggregate),
                ],
            ],
            'labels' => $suppliers->map(fn (\Flowframe\Trend\TrendValue $trend) => now()->parse($trend->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
