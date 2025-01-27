<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\Filament\Admin\Resources\DocumentResource;
use App\Models\Document;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

// use Filament\Support\Colors\Color;
// use Filament\Support\Enums\Alignment;
// use Filament\Support\Enums\MaxWidth;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    public function getView(): string
    {
        return $this->getRecord()->type === \App\Enums\DocumentType::Invoice->value
        ? 'filament.admin.resources.document-resource.pages.view-invoice'
        : 'filament.admin.resources.document-resource.pages.view-receipt';
    }

    public function getQrCode(): string
    {
        return (new Builder(
            writer: new SvgWriter,
            data: $this->getRecord()->uuid,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            backgroundColor: new Color(24, 24, 27),
            foregroundColor: new Color(255, 255, 255),
            logoPath: 'images/logo-dark.svg',
            logoResizeToWidth: 25,
            logoResizeToHeight: 12.5,
            logoPunchoutBackground: true,
            size: 100,
        ))
            ->build()
            ->getDataUri();
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        if ($record->type === \App\Enums\DocumentType::Invoice->value) {
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
                        || $record->payment_status === \App\Enums\PaymentStatus::Paid->value;
                    })
                    ->fillForm(fn (Document $record) => [
                        'amount' => $record->amount,
                        'amount_paid' => $record->amount_paid,
                        'amount_due' => round($record->amount - $record->amount_paid, 2),
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
                        $record->documentable()->update([
                            'tendered' => $record->amount_paid,
                        ]);

                        if ($record->amount_paid === $record->amount) {
                            $record->update([
                                'payment_status' => \App\Enums\PaymentStatus::Paid->value,
                                'payment_date' => now(),
                                'type' => \App\Enums\DocumentType::Receipt->value,
                            ]);
                            $record->documentable()->update([
                                'payment_status' => \App\Enums\PaymentStatus::Paid->value,
                            ]);

                            Notification::make('cleared')
                                ->title('Cleared')
                                ->body('The invoice has been cleared out and a receipt was issued.')
                                ->success()
                                ->send();
                        } else {
                            $record->update([
                                'payment_status' => \App\Enums\PaymentStatus::Pending->value,
                                'payment_date' => null,
                            ]);

                            $record->documentable()->update([
                                'payment_status' => \App\Enums\PaymentStatus::Pending->value,
                            ]);

                            Notification::make('paid')
                                ->title('Paid')
                                ->body('There are still payments due for that invoice.')
                                ->info()
                                ->send();
                        }
                    }),
                // Actions\Action::make('download')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->color(Color::Emerald)
                //     ->modalWidth(MaxWidth::ExtraSmall)
                //     ->modalHeading('Invoice')
                //     ->modalSubmitActionLabel('Download')
                //     ->modalFooterActionsAlignment(Alignment::Center)
                //     ->form([
                //         Forms\Components\Select::make('format')
                //             ->options(\App\PaperFormat::class)
                //             ->default(\App\PaperFormat::A4->value)
                //             ->searchable()
                //             ->required(),
                //         Forms\Components\Select::make('orientation')
                //             ->options(\App\PaperOrientation::class)
                //             ->default(\App\PaperOrientation::Portrait->value)
                //             ->searchable()
                //             ->required(),
                //     ])
                //     ->action(function (array $data) use ($record) {
                //         $open = "open('".route('invoice.download', ['record' => $record->documentable, 'format' => $data['format'], 'orientation' => $data['orientation']])."', '_blank').focus()";
                //         $this->js($open);

                //         Notification::make('download')
                //             ->title('Downloaded')
                //             ->icon('heroicon-o-check-circle')
                //             ->success()
                //             ->send();
                //     }),
                Actions\Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(fn () => $this->js('print()')),
            ];
        }

        return [
            Actions\EditAction::make()
                ->color('gray'),
            // Actions\Action::make('download')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color(Color::Emerald)
            //     ->modalWidth(MaxWidth::ExtraSmall)
            //     ->modalHeading('Receipt')
            //     ->modalSubmitActionLabel('Download')
            //     ->form([
            //         Forms\Components\Select::make('format')
            //             ->options(\App\PaperFormat::class)
            //             ->default(\App\PaperFormat::A4->value)
            //             ->searchable()
            //             ->required(),
            //         Forms\Components\Select::make('orientation')
            //             ->options(\App\PaperOrientation::class)
            //             ->default(\App\PaperOrientation::Portrait->value)
            //             ->searchable()
            //             ->required(),
            //     ])
            //     ->action(function (array $data) use ($record) {
            //         $open = "open('".route('receipt.download', ['record' => $record->documentable, 'format' => $data['format'], 'orientation' => $data['orientation']])."', '_blank').focus()";
            //         $this->js($open);

            //         Notification::make('download')
            //             ->title('Downloaded')
            //             ->icon('heroicon-o-check-circle')
            //             ->success()
            //             ->send();
            //     }),
            Actions\Action::make('print')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(fn () => $this->js('print()')),
        ];
    }
}
