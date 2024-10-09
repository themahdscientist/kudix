<?php

namespace App\Filament\Admin\Resources\ClientResource\Pages;

use App\Filament\Admin\Resources\ClientResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    public ?array $client_info;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['client_info'] = $this->getRecord()->clientInfo->attributesToArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->client_info = $data['client_info'];
        unset($data['client_info']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->getRecord()->clientInfo()->update($this->client_info);
    }
}
