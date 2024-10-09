<?php

namespace App\Filament\Admin\Resources\SaleResource\Pages;

use App\Filament\Admin\Resources\SaleResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
