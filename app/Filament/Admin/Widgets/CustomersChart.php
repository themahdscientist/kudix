<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Role;
use Filament\Widgets\ChartWidget;

class CustomersChart extends ChartWidget
{
    protected static ?string $heading = 'Customers';

    protected static string $color = 'success';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $customers = \Flowframe\Trend\Trend::query(Customer::query()->where('role_id', Role::CUSTOMER))
            ->between(now()->startOfYear(), now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => $customers->map(fn (\Flowframe\Trend\TrendValue $trend) => $trend->aggregate),
                ],
            ],
            'labels' => $customers->map(fn (\Flowframe\Trend\TrendValue $trend) => now()->parse($trend->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
