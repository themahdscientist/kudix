<?php

namespace App\Filament\Admin\Resources\SalespersonResource\Pages;

use App\Filament\Admin\Resources\SalespersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageSalespeople extends ManageRecords
{
    protected static string $resource = SalespersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::FitContent),
        ];
    }
}
