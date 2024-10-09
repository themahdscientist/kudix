<?php

namespace App\Filament\Admin\Resources\SupplierResource\Pages;

use App\Filament\Admin\Resources\SupplierResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
