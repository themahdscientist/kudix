<?php

namespace App\Filament\Admin\Resources\DoctorResource\Pages;

use App\Filament\Admin\Resources\DoctorResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
