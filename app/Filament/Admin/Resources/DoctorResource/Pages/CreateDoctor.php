<?php

namespace App\Filament\Admin\Resources\DoctorResource\Pages;

use App\Filament\Admin\Resources\DoctorResource;
use App\Filament\Resources\Pages\CreateRecord;
use App\Models\Role;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected function afterCreate(): void
    {
        \App\Utils::furnishUser($this->getRecord(), Role::DOCTOR);
    }
}
