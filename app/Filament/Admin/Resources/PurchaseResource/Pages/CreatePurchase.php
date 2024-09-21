<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\PurchaseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Buy')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }
}
