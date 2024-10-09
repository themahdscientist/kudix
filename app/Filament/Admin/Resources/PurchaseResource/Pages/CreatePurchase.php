<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use App\Filament\Admin\Resources\PurchaseResource;
use App\Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use NumberFormatter;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    public ?array $document;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Buy')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('Buy & buy another'))
            ->action('createAnother')
            ->keyBindings(['mod+shift+s'])
            ->color('gray');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->document = $data['document'];
        unset($data['document']);

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->getRecord();
        $change = round($record->tendered - $record->total_cost);
        $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);

        return Notification::make()
            ->icon('heroicon-o-banknotes')
            ->title(fn () => $change >= 0 ? 'Change due' : 'Balance due')
            ->body(fn () => $change >= 0 ? "{$fmt->formatCurrency(abs($change), 'NGN')}.<br>A receipt has been generated for this purchase." : "{$fmt->formatCurrency(abs($change), 'NGN')}.<br>An invoice has been generated for this purchase.")
            ->status(fn () => $change >= 0 ? 'success' : 'danger')
            ->persistent()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view-document')
                    ->label(fn () => $change >= 0 ? 'Receipt' : 'Invoice')
                    ->icon(fn () => $change >= 0 ? 'heroicon-o-receipt-percent' : 'heroicon-o-document-text')
                    ->url(fn () => DocumentResource::getUrl('view', [$record->document])),
            ])
            ->send();
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        if ($record->tendered < $record->total_cost || $record->payment_status !== \App\PaymentStatus::Paid->value) {
            $record->document()->create([
                'amount' => $record->total_cost,
                'amount_paid' => $record->tendered,
                'due_date' => $this->document['due_date'],
                'payment_date' => null,
                'payment_status' => \App\PaymentStatus::Pending->value,
                'type' => \App\DocumentType::Invoice->value,
                'uuid' => \App\Utils::generateDocumentId(),
            ]);
        } else {
            $record->document()->create([
                'amount' => $record->total_cost,
                'amount_paid' => $record->tendered,
                'due_date' => now(),
                'payment_date' => now(),
                'payment_status' => \App\PaymentStatus::Paid->value,
                'type' => \App\DocumentType::Receipt->value,
                'uuid' => \App\Utils::generateDocumentId(),
            ]);
        }
    }
}
