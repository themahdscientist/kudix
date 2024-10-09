<?php

namespace App\Filament\Admin\Resources\ProductResource\Widgets;

use App\Filament\Admin\Resources\ProductResource\Pages\ListProducts;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpiredProducts extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        return [
            // Stat::make('All', $this->getPageTableRecords()->count())
            //     ->icon('heroicon-s-sparkles'),
            //     Stat::make('Expired', $this->getPageTableQuery()->where('expiry_date', '<=', now())->count())
            //     ->icon('heroicon-s-exclamation-circle'),
        ];
    }
}
