<?php

namespace App\Filament\Admin\Resources\CashierResource\Pages;

use App\Filament\Admin\Resources\CashierResource;
use App\Filament\Resources\Pages\CreateRecord;
use App\Models\Role;

class CreateCashier extends CreateRecord
{
    protected static string $resource = CashierResource::class;

    protected function afterCreate(): void
    {
        \App\Utils::furnishUser($this->getRecord(), Role::CASHIER);
    }
}
