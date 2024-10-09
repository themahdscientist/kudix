<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

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
                ->badge(fn () => static::getResource()::getEloquentQuery()->whereDate('created_at', now())->count()),
            'yesterday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', now()->subDay()))
                ->icon('heroicon-s-clock')
                ->badge(fn () => static::getResource()::getEloquentQuery()->whereDate('created_at', now()->subDay())->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'today';
    }
}
