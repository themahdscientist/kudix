<?php

namespace App\Filament\Admin\Resources\CustomerResource\Pages;

use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Resources\Pages\CreateRecord;
use App\Models\Role;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    public ?array $customer_info;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->customer_info = $data['customer_info'];
        unset($data['customer_info']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        $record->customerInfo()->create($this->customer_info);
        $record->role()->associate(Role::CUSTOMER)->save();
    }
}
