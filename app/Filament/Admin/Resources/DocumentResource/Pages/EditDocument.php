<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use App\Filament\Resources\Pages\EditRecord;
use App\Models\Document;
use Filament\Actions;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->hidden(fn (Document $record) => $record->trashed()),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
