<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\Purchase;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $activeNavigationIcon = 'heroicon-s-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('metadata')
                    ->label('UUID & Type')
                    ->schema([
                        Forms\Components\TextInput::make('uuid')
                            ->label('')
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
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->label('')
                            ->options(\App\DocumentType::class)
                            ->default(\App\DocumentType::Invoice->value)
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
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
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
                    ->afterStateUpdated(function (mixed $state, Forms\Set $set) {
                        if ($state['type'] === \App\DocumentType::Invoice->value) {
                            if ($state['documentable_type'] === 'purchase' && ! is_null($state['documentable_id'])) {
                                // ! remove the columns if u want relationships to load
                                $purchase = Purchase::query()->find($state['documentable_id'], [
                                    'created_at',
                                    'payment_status',
                                    'tendered',
                                    'total_price',
                                ]);
                                $set('amount', $purchase->total_price);
                                $set('amount_paid', $purchase->tendered);
                            } elseif ($state['documentable_type'] === 'sale' && ! is_null($state['documentable_id'])) {
                                $sale = Sale::query()->find($state['documentable_id'])->load('document');
                                $status = false;

                                if ($sale->document()->where('type', 'invoice')->exists() || $sale->tendered >= $sale->total_cost || $sale->payment_status === \App\PaymentStatus::Paid->value) {
                                    $set('amount', null);
                                    $set('amount_paid', null);
                                    $set('created_at', null);
                                    $set('payment_status', null);
                                } else {
                                    $set('amount', $sale->total_cost);
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
                        } elseif ($state['type'] === \App\DocumentType::Receipt->value) {
                            if ($state['documentable_type'] === 'purchase' && ! is_null($state['documentable_id'])) {
                                // ! remove the columns if u want relationships to load
                                $purchase = Purchase::query()->find($state['documentable_id'], [
                                    'created_at',
                                    'payment_status',
                                    'tendered',
                                    'total_price',
                                ]);
                                $set('amount', $purchase->total_price);
                                $set('amount_paid', $purchase->tendered);
                            } elseif ($state['documentable_type'] === 'sale' && ! is_null($state['documentable_id'])) {
                                $sale = Sale::query()->find($state['documentable_id'])->load('document');
                                $status = false;

                                if ($sale->document()->where('type', 'receipt')->exists() || $sale->tendered < $sale->total_cost || $sale->payment_status !== \App\PaymentStatus::Paid->value) {
                                    $set('amount', null);
                                    $set('amount_paid', null);
                                    $set('created_at', null);
                                    $set('payment_status', null);
                                } else {
                                    $set('amount', $sale->total_cost);
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
                    ->options(\App\PaymentStatus::class)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Document ID')
                    ->color(Color::Neutral)
                    ->searchable(),
                Tables\Columns\TextColumn::make('documentable.uuid')
                    ->label('Documentable ID')
                    ->color(Color::Neutral)
                    ->tooltip(fn ($record) => Str::ucfirst($record->documentable_type))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN')
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('NGN')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Issued date')
                    ->date()
                    ->timeTooltip()
                    ->sortable()
                    ->summarize(Summarizers\Count::make()->numeric()->prefix('Documents: ')),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->tooltip(function (Document $record): string {
                        if ($record->payment_status === \App\PaymentStatus::Paid->value) {
                            return 'Paid';
                        }

                        return $record->due_date->isFuture()
                        ? $record->due_date->diffForHumans()
                        : ($record->due_date->isToday() ? 'Due today!' : 'Over due!');
                    })
                    ->color(function (Document $record): string {
                        if ($record->payment_status === \App\PaymentStatus::Paid->value) {
                            return 'success';
                        }

                        return $record->due_date->isFuture()
                        ? 'info'
                        : ($record->due_date->isToday() ? 'warning' : 'danger');
                    })
                    ->icon(function (Document $record): string {
                        if ($record->payment_status === \App\PaymentStatus::Paid->value) {
                            return 'heroicon-s-check-circle';
                        }

                        return $record->due_date->isFuture()
                        ? 'heroicon-s-arrow-path-rounded-square'
                        : ($record->due_date->isToday() ? 'heroicon-s-exclamation-circle' : 'heroicon-s-x-circle');
                    })
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable()
                    ->placeholder('pending finalization...')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('payment_status')
                    ->icon(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getIcon())
                    ->color(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getColor())
                    ->tooltip(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getLabel()),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-s-credit-card')
                    ->modalSubmitActionLabel('Pay')
                    ->icon('heroicon-s-credit-card')
                    ->iconButton()
                    ->hidden(function (Document $record) {
                        return $record->type === \App\DocumentType::Receipt->value || $record->amount_paid >= $record->amount
                        || $record->payment_status === \App\PaymentStatus::Paid->value || $record->trashed();
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
                                'payment_status' => \App\PaymentStatus::Paid->value,
                                'payment_date' => now(),
                                'type' => \App\DocumentType::Receipt->value,
                            ]);
                            $record->documentable->update([
                                'payment_status' => \App\PaymentStatus::Paid->value,
                            ]);

                            Notification::make('cleared')
                                ->title('Cleared')
                                ->body('The invoice has been cleared out and a receipt was issued.')
                                ->success()
                                ->send();
                        } else {
                            $record->update([
                                'payment_status' => \App\PaymentStatus::Pending->value,
                                'payment_date' => null,
                            ]);

                            $record->documentable->update([
                                'payment_status' => \App\PaymentStatus::Pending->value,
                            ]);

                            Notification::make('paid')
                                ->title('Paid')
                                ->body('There are still payments due for that invoice.')
                                ->info()
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make()
                    ->hidden(fn (Document $record) => $record->trashed()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('status')
                        ->label('Update selected')
                        ->modalHeading('Update selected invoices')
                        ->color('info')
                        ->icon('heroicon-s-arrow-path')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->hidden(function () {
                            if (request()->query('tableFilters')) {
                                return request()->query('tableFilters')['trashed']['value'] === '0';
                            }

                            return false;
                        })
                        ->form([
                            Forms\Components\Select::make('payment_status')
                                ->options(\App\PaymentStatus::class)
                                ->default(\App\PaymentStatus::Pending)
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $records->each(function (Document $record) use ($data) {
                                if ($record->type === \App\DocumentType::Receipt->value) {
                                    return Notification::make('status')
                                        ->title('Ignored')
                                        ->body('A receipt\'s payment status cannot be updated.')
                                        ->info()
                                        ->send();
                                }

                                $record->update(['payment_status' => $data['payment_status']]);

                                Notification::make('status')
                                    ->title('Updated')
                                    ->success()
                                    ->send();
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['documentable.products']);
    }
}
