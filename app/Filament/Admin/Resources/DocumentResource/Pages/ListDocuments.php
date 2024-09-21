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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'invoice'))
                ->badge(\App\Models\Document::query()->where('type', 'invoice')->count())
                ->icon('heroicon-s-document-text'),
                'receipts' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'receipt'))
                ->badge(\App\Models\Document::query()->where('type', 'receipt')->count())
                ->icon('heroicon-s-receipt-percent'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'receipts';
    }
}
