<?php

namespace App\Filament\Admin\Resources\ClientResource\Pages;

use App\Filament\Admin\Resources\ClientResource;
use App\Filament\Resources\Pages\CreateRecord;
use App\Models\Role;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public ?array $client_info;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->client_info = $data['client_info'];
        unset($data['client_info']);

        return $data;
    }

    protected function afterCreate(): void
    {
        \App\Utils::furnishUser($this->getRecord(), Role::CLIENT, $this->client_info);
    }
}
