<?php

namespace App\Filament\Admin\Resources\CustomerResource\Pages;

use App\Filament\Admin\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'regulars' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas('customerInfo', function (Builder $query) {
                        $query->where('customers.type', \App\Enums\CustomerType::Regular->value);
                    })
                )
                ->icon('heroicon-s-swatch')
                ->badge(
                    fn () => static::getResource()::getModel()::query()->whereHas('customerInfo', function (Builder $query) {
                        $query->where('customers.type', \App\Enums\CustomerType::Regular->value);
                    })
                        ->count()
                ),
            'patients' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas('customerInfo', function (Builder $query) {
                        $query->where('customers.type', \App\Enums\CustomerType::Patient->value);
                    })
                )
                ->icon('heroicon-s-scissors')
                ->badge(
                    fn () => static::getResource()::getModel()::query()->whereHas('customerInfo', function (Builder $query) {
                        $query->where('customers.type', \App\Enums\CustomerType::Patient->value);
                    })
                        ->count()
                ),
        ];
    }
}
