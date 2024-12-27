<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'invoices' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', \App\DocumentType::Invoice->value))
                ->icon('heroicon-s-document-text')
                ->badge(fn () => static::getResource()::getModel()::query()->where('type', \App\DocumentType::Invoice->value)->count()),
            'receipts' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', \App\DocumentType::Receipt->value))
                ->icon('heroicon-s-receipt-percent')
                ->badge(fn () => static::getResource()::getModel()::query()->where('type', \App\DocumentType::Receipt->value)->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'receipts';
    }
}
