<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Client;
use App\Models\Role;
use Filament\Widgets\ChartWidget;

class ClientsChart extends ChartWidget
{
    protected static ?string $heading = 'Clients';

    protected static string $color = 'warning';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $clients = \Flowframe\Trend\Trend::query(Client::query()->where('role_id', Role::CLIENT))
            ->between(now()->startOfYear(), now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Clients',
                    'data' => $clients->map(fn (\Flowframe\Trend\TrendValue $trend) => $trend->aggregate),
                ],
            ],
            'labels' => $clients->map(fn (\Flowframe\Trend\TrendValue $trend) => now()->parse($trend->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
