<?php

namespace App\Filament\Admin\Resources\SaleResource\Pages;

use App\Filament\Admin\Resources\SaleResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Sell')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCreatedNotification(): ?Notification
    {
        $change = round($this->getRecord()->tendered - $this->getRecord()->total_cost);

        return Notification::make()
            ->icon('heroicon-o-banknotes')
            ->title('Change due')
            ->body("NGN{$change}")
            ->status(fn () => $change >= 0 ? 'success' : 'danger')
            ->send();
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        if ($record->tendered < $record->total_cost || $record->payment_status !== \App\SalePaymentStatus::Paid->value) {
            $record->document()->create([
                'amount' => $record->total_cost,
                'amount_paid' => $record->tendered,
                'due_date' => $this->form->getState()['due_date'],
                'payment_date' => null,
                'payment_status' => \App\SalePaymentStatus::Pending->value,
                'type' => \App\DocumentType::Invoice->value,
                'uuid' => \App\Utils::generateDocumentId(),
            ]);
        } else {
            $record->document()->create([
                'amount' => $record->total_cost,
                'amount_paid' => $record->tendered,
                'due_date' => now(),
                'payment_date' => now(),
                'payment_status' => \App\SalePaymentStatus::Paid->value,
                'type' => \App\DocumentType::Receipt->value,
                'uuid' => \App\Utils::generateDocumentId(),
            ]);
        }
    }
}
