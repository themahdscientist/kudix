<?php

namespace App\Filament\Admin\Resources\SaleResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use App\Filament\Admin\Resources\SaleResource;
use App\Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use NumberFormatter;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    public ?array $document;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Sell')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('Sell & sell another'))
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
        $change = round($this->getRecord()->tendered - $this->getRecord()->total_price);
        $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);

        return Notification::make()
            ->icon('heroicon-o-banknotes')
            ->title(fn () => $change >= 0 ? 'Change due' : 'Balance due')
            ->body(fn () => $change >= 0 ? "{$fmt->formatCurrency(abs($change), 'NGN')}.<br>A receipt has been generated for this sale." : "{$fmt->formatCurrency(abs($change), 'NGN')}.<br>An invoice has been generated for this sale.")
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
        if ($record->tendered < $record->total_price || $record->payment_status !== \App\Enums\PaymentStatus::Paid->value) {
            $record->document()->create([
                'amount' => $record->total_price,
                'amount_paid' => $record->tendered,
                'due_date' => $this->form->getState()['document']['due_date'],
                'payment_date' => null,
                'payment_status' => \App\Enums\PaymentStatus::Pending->value,
                'type' => \App\Enums\DocumentType::Invoice->value,
                'uuid' => \App\Utils::generateDocumentId(),
            ]);
        } else {
            $record->document()->create([
                'amount' => $record->total_price,
                'amount_paid' => $record->tendered,
                'due_date' => now(),
                'payment_date' => now(),
                'payment_status' => \App\Enums\PaymentStatus::Paid->value,
                'type' => \App\Enums\DocumentType::Receipt->value,
                'uuid' => \App\Utils::generateDocumentId(),
            ]);
        }
    }
}
