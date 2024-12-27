<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected ?string $heading = 'Stats Overview';
    
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $totalPurchases = Purchase::count();
        $totalSales = Sale::count();

        return [
            Stat::make('Products', $totalProducts),
            Stat::make('Purchases', $totalPurchases),
            Stat::make('Sales', $totalSales),
        ];
    }
}
