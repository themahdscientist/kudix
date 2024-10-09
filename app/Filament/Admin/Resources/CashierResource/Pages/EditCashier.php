<?php

namespace App\Filament\Admin\Resources\CashierResource\Pages;

use App\Filament\Admin\Resources\CashierResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCashier extends EditRecord
{
    protected static string $resource = CashierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
