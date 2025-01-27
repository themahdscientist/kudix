<?php

namespace App\Filament\Admin\Resources\CustomerResource\Pages;

use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public ?array $customer_info;

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
        $data['customer_info'] = $this->getRecord()->customerInfo->attributesToArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->customer_info = $data['customer_info'];
        unset($data['customer_info']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->getRecord()->customerInfo()->update($this->customer_info);
    }
}
