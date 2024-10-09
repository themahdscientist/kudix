<?php

namespace App\Filament\Admin\Resources\CashierResource\Pages;

use App\Filament\Admin\Resources\CashierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashiers extends ListRecords
{
    protected static string $resource = CashierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
