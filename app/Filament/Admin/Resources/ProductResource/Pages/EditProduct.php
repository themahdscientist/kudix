<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
