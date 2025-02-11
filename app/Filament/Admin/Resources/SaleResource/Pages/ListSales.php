<?php

namespace App\Filament\Admin\Resources\SaleResource\Pages;

use App\Filament\Admin\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon('heroicon-s-sparkles')
                ->badge(fn () => static::getResource()::getModel()::count()),
            'today' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', now()))
                ->icon('heroicon-s-calendar')
                ->badge(fn () => static::getResource()::getModel()::query()->whereDate('created_at', now())->count()),
            'yesterday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', now()->subDay()))
                ->icon('heroicon-s-clock')
                ->badge(fn () => static::getResource()::getModel()::query()->whereDate('created_at', now()->subDay())->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'today';
    }
}
