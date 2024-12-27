<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // ProductResource\Widgets\ExpiredProducts::class,
        ];
    }

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
            'viable' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->whereNot('status', \App\ProductStatus::OutOfStock->value)
                        ->whereNot('status', \App\ProductStatus::Discontinued->value)
                        ->whereDate('expiry_date', '>', now());
                }))
                ->icon('heroicon-s-shield-check')
                ->badge(fn () => static::getResource()::getModel()::query()->where(function (Builder $query) {
                    $query->whereNot('status', \App\ProductStatus::OutOfStock->value)
                        ->whereNot('status', \App\ProductStatus::Discontinued->value)
                        ->whereDate('expiry_date', '>', now());
                })->count()),
            'expired' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('expiry_date', '<=', now()))
                ->icon('heroicon-s-trash')
                ->badge(fn () => static::getResource()::getModel()::query()->whereDate('expiry_date', '<=', now())->count()),
            'out-of-stock' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', \App\ProductStatus::OutOfStock->value))
                ->icon('heroicon-s-minus-circle')
                ->badge(fn () => static::getResource()::getModel()::query()->where('status', \App\ProductStatus::OutOfStock->value)->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'viable';
    }
}
