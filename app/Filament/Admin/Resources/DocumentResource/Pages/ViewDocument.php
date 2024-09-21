<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use App\Models\Document;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    public function getView(): string
    {
        return $this->getRecord()->type === \App\DocumentType::Invoice->value
        ? 'filament.admin.resources.document-resource.pages.view-invoice'
        : 'filament.admin.resources.document-resource.pages.view-receipt';
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        if ($record->type === \App\DocumentType::Invoice->value) {
            return [
                Actions\EditAction::make()
                    ->color('gray'),
                Actions\Action::make('pay')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-s-credit-card')
                    ->modalSubmitActionLabel('Pay')
                    ->icon('heroicon-o-credit-card')
                    ->hidden(function (Document $record) {
                        return $record->amount_paid === $record->amount
                        || $record->payment_status === \App\PurchasePaymentStatus::Paid->value;
                    })
                    ->fillForm(fn (Document $record) => [
                        'amount' => $record->amount,
                        'amount_paid' => $record->amount_paid,
                        'amount_due' => $record->amount - $record->amount_paid,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_due')
                            ->numeric()
                            ->minValue(1.00)
                            ->maxValue(fn (Document $record) => $record->amount - $record->amount_paid)
                            ->required(),
                    ])
                    ->action(function (array $data, Document $record) {
                        $record->update([
                            'amount_paid' => $record->amount_paid + $data['amount_due'],
                        ]);
                        $record->documentable->update([
                            'tendered' => $record->amount_paid,
                        ]);

                        if ($record->amount_paid === $record->amount) {
                            $record->update([
                                'payment_status' => \App\PurchasePaymentStatus::Paid->value,
                                'payment_date' => now(),
                            ]);
                            $record->documentable->update([
                                'payment_status' => \App\SalePaymentStatus::Paid->value,
                            ]);

                            Notification::make('cleared')
                                ->title('Invoice cleared')
                                ->body('The invoice has been paid out.')
                                ->success()
                                ->send();
                        } else {
                            $record->update([
                                'payment_status' => \App\PurchasePaymentStatus::Pending->value,
                                'payment_date' => null,
                            ]);

                            $record->documentable->update([
                                'payment_status' => \App\SalePaymentStatus::Pending->value,
                            ]);

                            Notification::make('paid')
                                ->title('Invoice paid')
                                ->body('There are payments due.')
                                ->info()
                                ->send();
                        }
                    }),
                Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color(Color::Emerald)
                    ->modalWidth(MaxWidth::ExtraSmall)
                    ->modalHeading('Invoice')
                    ->modalSubmitActionLabel('Download')
                    ->form([
                        Forms\Components\Select::make('format')
                            ->options(\Spatie\LaravelPdf\Enums\Format::class)
                            ->default(\Spatie\LaravelPdf\Enums\Format::A4->value)
                            ->required(),
                        Forms\Components\Select::make('orientation')
                            ->options(\Spatie\LaravelPdf\Enums\Orientation::class)
                            ->default(\Spatie\LaravelPdf\Enums\Orientation::Portrait->value)
                            ->required(),
                    ])
                    ->action(function (array $data) use ($record) {
                        $open = "open('".route('invoice.download', ['record' => $record->documentable, 'format' => $data['format'], 'orientation' => $data['orientation']])."', '_blank').focus()";
                        $this->js($open);

                        Notification::make('download')
                            ->title('Downloaded')
                            ->icon('heroicon-o-check-circle')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(fn () => $this->js('print()')),
            ];
        }

        return [
            Actions\EditAction::make()
                ->color('gray'),
            Actions\Action::make('download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color(Color::Emerald)
                ->modalWidth(MaxWidth::ExtraSmall)
                ->modalHeading('Invoice')
                ->modalSubmitActionLabel('Download')
                ->form([
                    Forms\Components\Select::make('format')
                        ->options(\Spatie\LaravelPdf\Enums\Format::class)
                        ->default(\Spatie\LaravelPdf\Enums\Format::A4->value)
                        ->required(),
                    Forms\Components\Select::make('orientation')
                        ->options(\Spatie\LaravelPdf\Enums\Orientation::class)
                        ->default(\Spatie\LaravelPdf\Enums\Orientation::Portrait->value)
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $open = "open('".route('receipt.download', ['record' => $record->documentable, 'format' => $data['format'], 'orientation' => $data['orientation']])."', '_blank').focus()";
                    $this->js($open);

                    Notification::make('download')
                        ->title('Downloaded')
                        ->icon('heroicon-o-check-circle')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('print')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(fn () => $this->js('print()')),
        ];
    }
}
