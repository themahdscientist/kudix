<?php

namespace App\Filament\Admin\Resources\ClientResource\Pages;

use App\Filament\Admin\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'customers' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas('clientInfo', function (Builder $query) {
                        $query->where('clients.type', \App\ClientType::Customer->value);
                    })
                )
                ->icon('heroicon-s-swatch')
                ->badge(
                    fn () => static::getResource()::getModel()::query()->whereHas('clientInfo', function (Builder $query) {
                        $query->where('clients.type', \App\ClientType::Customer->value);
                    })
                        ->count()
                ),
            'patients' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas('clientInfo', function (Builder $query) {
                        $query->where('clients.type', \App\ClientType::Patient->value);
                    })
                )
                ->icon('heroicon-s-scissors')
                ->badge(
                    fn () => static::getResource()::getModel()::query()->whereHas('clientInfo', function (Builder $query) {
                        $query->where('clients.type', \App\ClientType::Patient->value);
                    })
                        ->count()
                ),
        ];
    }
}
