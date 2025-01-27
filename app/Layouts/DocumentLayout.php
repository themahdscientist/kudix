<?php

namespace App\Layouts;

use App\Models\Document;
use App\Models\Purchase;
use App\Models\Sale;
use Filament\Forms;
use Filament\Notifications\Notification;

abstract class DocumentLayout
{
    public static function getForm(): array
    {
        $base = [
            Forms\Components\Split::make([
                Forms\Components\Section::make('Document details')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(1.00)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(1.00)
                            ->maxValue(fn (Forms\Get $get) => $get('amount'))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Issued date')
                            ->default(now()->toDateTimeString())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(now()->toDateTimeString())
                            ->minDate(fn (?Document $record, string $operation) => $operation === 'create' ? now()->toDateString() : $record->due_date)
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->hintIcon('heroicon-o-question-mark-circle')
                            ->hintIconTooltip('This is filled when the invoice is paid out or on receipt issuance.')
                            ->hintColor('info')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('payment_status')
                            ->options(\App\Enums\PaymentStatus::class)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ]),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('uuid')
                        ->label('UUID')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('regenerate')
                                ->icon('heroicon-o-arrow-path')
                                ->iconButton()
                                ->action(fn (Forms\Set $set) => $set('uuid', \App\Utils::generateDocumentId()))
                        )
                        ->default(\App\Utils::generateDocumentId())
                        ->disabled()
                        ->dehydrated(fn (string $operation) => $operation === 'create')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('type')
                        ->options(\App\Enums\DocumentType::class)
                        ->default(\App\Enums\DocumentType::Invoice->value)
                        ->disabledOn('edit')
                        ->live(true)
                        ->native(false)
                        ->required()
                        ->afterStateUpdated(function (Forms\Set $set) {
                            $set('documentable_type', null);
                            $set('documentable_id', null);
                            $set('amount', null);
                            $set('amount_paid', null);
                            $set('created_at', null);
                            $set('payment_status', null);
                        }),
                    Forms\Components\MorphToSelect::make('documentable')
                        ->types([
                            Forms\Components\MorphToSelect\Type::make(Purchase::class)
                                ->titleAttribute('uuid'),
                            Forms\Components\MorphToSelect\Type::make(Sale::class)
                                ->titleAttribute('uuid'),
                        ])
                        ->searchable()
                        ->preload()
                        ->live(true)
                        ->native(false)
                        ->required()
                        ->disabledOn('edit')
                        ->columnSpanFull()
                        ->afterStateUpdated(function (mixed $state, Forms\Set $set) {
                            if ($state['type'] === \App\Enums\DocumentType::Invoice->value) {
                                if ($state['documentable_type'] === 'purchase' && ! is_null($state['documentable_id'])) {
                                    $purchase = Purchase::query()->find($state['documentable_id'])->load('document');
                                    $status = false;

                                    if ($purchase->document()->where('type', 'invoice')->exists() || $purchase->tendered >= $purchase->total_cost || $purchase->payment_status === \App\Enums\PaymentStatus::Paid->value) {
                                        $set('amount', null);
                                        $set('amount_paid', null);
                                        $set('created_at', null);
                                        $set('payment_status', null);
                                    } else {
                                        $set('amount', $purchase->total_cost);
                                        $set('amount_paid', $purchase->tendered);
                                        $set('created_at', $purchase->created_at->toDateTimeString());
                                        $set('payment_status', $purchase->payment_status);

                                        $status = true;
                                    }

                                    return Notification::make('status')
                                        ->title($purchase->uuid)
                                        ->body(fn (): string => $status
                                        ? 'This purchase has no invoice and hasn\'t been paid for. You may proceed.'
                                        : 'This purchase either has an invoice or has already been paid for. You cannot proceed.')
                                        ->status(fn (): string => $status ? 'success' : 'danger')
                                        ->persistent()
                                        ->send();

                                } elseif ($state['documentable_type'] === 'sale' && ! is_null($state['documentable_id'])) {
                                    $sale = Sale::query()->find($state['documentable_id'])->load('document');
                                    $status = false;

                                    if ($sale->document()->where('type', 'invoice')->exists() || $sale->tendered >= $sale->total_price || $sale->payment_status === \App\Enums\PaymentStatus::Paid->value) {
                                        $set('amount', null);
                                        $set('amount_paid', null);
                                        $set('created_at', null);
                                        $set('payment_status', null);
                                    } else {
                                        $set('amount', $sale->total_price);
                                        $set('amount_paid', $sale->tendered);
                                        $set('created_at', $sale->created_at->toDateTimeString());
                                        $set('payment_status', $sale->payment_status);

                                        $status = true;
                                    }

                                    return Notification::make('status')
                                        ->title($sale->uuid)
                                        ->body(fn (): string => $status
                                        ? 'This sale has no invoice and hasn\'t been paid for. You may proceed.'
                                        : 'This sale either has an invoice or has already been paid for. You cannot proceed.')
                                        ->status(fn (): string => $status ? 'success' : 'danger')
                                        ->persistent()
                                        ->send();
                                }
                            } elseif ($state['type'] === \App\Enums\DocumentType::Receipt->value) {
                                if ($state['documentable_type'] === 'purchase' && ! is_null($state['documentable_id'])) {
                                    $purchase = Purchase::query()->find($state['documentable_id'])->load('document');
                                    $status = false;

                                    if ($purchase->document()->where('type', 'receipt')->exists() || $purchase->tendered < $purchase->total_cost || $purchase->payment_status !== \App\Enums\PaymentStatus::Paid->value) {
                                        $set('amount', null);
                                        $set('amount_paid', null);
                                        $set('created_at', null);
                                        $set('payment_status', null);
                                    } else {
                                        $set('amount', $purchase->total_cost);
                                        $set('amount_paid', $purchase->tendered);
                                        $set('created_at', $purchase->created_at->toDateTimeString());
                                        $set('payment_date', $purchase->updated_at->toDateTimeString());
                                        $set('payment_status', $purchase->payment_status);

                                        $status = true;
                                    }

                                    return Notification::make('status')
                                        ->title($purchase->uuid)
                                        ->body(fn (): string => $status
                                        ? 'This purchase has no receipt and has already been paid for. You may proceed.'
                                        : 'This purchase either has a receipt or hasn\'t been paid for. You cannot proceed.')
                                        ->status(fn (): string => $status ? 'success' : 'danger')
                                        ->persistent()
                                        ->send();

                                } elseif ($state['documentable_type'] === 'sale' && ! is_null($state['documentable_id'])) {
                                    $sale = Sale::query()->find($state['documentable_id'])->load('document');
                                    $status = false;

                                    if ($sale->document()->where('type', 'receipt')->exists() || $sale->tendered < $sale->total_price || $sale->payment_status !== \App\Enums\PaymentStatus::Paid->value) {
                                        $set('amount', null);
                                        $set('amount_paid', null);
                                        $set('created_at', null);
                                        $set('payment_status', null);
                                    } else {
                                        $set('amount', $sale->total_price);
                                        $set('amount_paid', $sale->tendered);
                                        $set('created_at', $sale->created_at->toDateTimeString());
                                        $set('payment_date', $sale->updated_at->toDateTimeString());
                                        $set('payment_status', $sale->payment_status);

                                        $status = true;
                                    }

                                    return Notification::make('status')
                                        ->title($sale->uuid)
                                        ->body(fn (): string => $status
                                        ? 'This sale has no receipt and has already been paid for. You may proceed.'
                                        : 'This sale either has a receipt or hasn\'t been paid for. You cannot proceed.')
                                        ->status(fn (): string => $status ? 'success' : 'danger')
                                        ->persistent()
                                        ->send();
                                }
                            }
                        }),
                ])
                    ->columns(2),
            ])
                ->columnSpanFull(),
        ];

        return $base;
    }
}
