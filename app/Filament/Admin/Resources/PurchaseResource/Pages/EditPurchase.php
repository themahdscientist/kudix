<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\PurchaseResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    public ?array $document;

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
        $data['document'] = $this->getRecord()->document->attributesToArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->document = $data['document'];
        unset($data['document']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->getRecord()->document()->update($this->document);
    }
}
