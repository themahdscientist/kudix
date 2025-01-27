<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Products';

    protected static string $color = 'info';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $products = \Flowframe\Trend\Trend::model(Product::class)
            ->between(now()->startOfYear(), now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => $products->map(fn (\Flowframe\Trend\TrendValue $trend) => $trend->aggregate),
                ],
            ],
            'labels' => $products->map(fn (\Flowframe\Trend\TrendValue $trend) => now()->parse($trend->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
