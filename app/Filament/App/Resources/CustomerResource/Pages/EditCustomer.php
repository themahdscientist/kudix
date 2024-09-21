<?php

namespace App\Filament\App\Resources\CustomerResource\Pages;

use App\Filament\App\Resources\CustomerResource;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;
}
