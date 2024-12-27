<?php

namespace App\Forms\Components;

use App\Models\Client;
use App\Models\Product;
use App\Models\Role;
use Filament\Forms;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SaleField extends Forms\Components\Field
{
    public static function getForm(): array
    {
        $base = [
            Forms\Components\Section::make([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name', fn (Builder $query) => $query->where('role_id', Role::CLIENT))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->manageOptionForm(ClientField::getForm())
                    ->fillEditOptionActionFormUsing(function (mixed $state) {
                        return Client::query()->find($state)->load('clientInfo')->toArray();
                    })
                    ->updateOptionUsing(function (array $data, Forms\Form $form) {
                        $client_info = $data['client_info'];
                        unset($data['client_info']);

                        $form->getRecord()?->update($data);
                        $form->getRecord()?->load('clientInfo')->clientInfo->update($client_info);
                    }),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('regenerate')
                            ->icon('heroicon-o-arrow-path')
                            ->iconButton()
                            ->action(fn (Forms\Components\TextInput $component) => $component->state(\App\Utils::generateSaleId()))
                    )
                    ->default(\App\Utils::generateSaleId())
                    ->disabled()
                    ->dehydrated(fn (string $operation) => $operation === 'create')
                    ->required()
                    ->unique(ignoreRecord: true),
            ])
                ->columns(),
            Forms\Components\Repeater::make('productSales')
                ->label('Sale order')
                ->relationship()
                ->addActionLabel('Add to cart')
                ->addable(fn (string $operation): bool => $operation === 'create')
                ->disabled(fn (string $operation): bool => $operation === 'edit')
                ->dehydrated(false)
                ->collapsed(fn (string $operation) => $operation === 'edit')
                ->itemLabel(fn (array $state): ?string => Product::query()->find($state['product_id'], 'name')->name ?? 'Cart Item')
                ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation())
                ->columnSpanFull()
                ->columns(\App\Utils::responsive())
                ->live(true)
                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                    $subtotal = 0;
                    foreach ($state as $item) {
                        $subtotal += $item['subtotal'];
                    }

                    $discount = (float) $get('discount') * $subtotal / 100;
                    $vat = (float) $get('vat') / 100;
                    $shipping = (float) $get('shipping');

                    $total = round(($subtotal - $discount) * (1 + $vat) + $shipping, 2);
                    $set('total_price', $total);
                    $set('tendered', $total);
                    $set('change', round($get('tendered') - $total));
                    $set('payment_status', \App\PaymentStatus::Paid->value);
                })
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            $set('unit_price', round(Product::query()->find($state, 'price')?->price, 2) ?? null);
                            $set('quantity', null);
                            $set('subtotal', null);
                            $set('../../tendered', 0);
                            $set('../../total_price', null);
                        })
                        ->manageOptionForm(ProductField::getForm()),
                    Forms\Components\TextInput::make('unit_price')
                        ->numeric()
                        ->prefix('₦')
                        ->minValue(1)
                        ->maxValue(42949672.95)
                        ->disabled()
                        ->dehydrated(fn (string $operation) => $operation === 'create'),
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(function (Forms\Get $get, string $operation): ?int {
                            if ($operation === 'create') {
                                return Product::query()->find($get('product_id'), 'quantity')->quantity;
                            }

                            return null;
                        })
                        ->validationMessages(['max' => 'The quantity has exceeded the available stock.'])
                        ->live(true)
                        ->disabled(fn (Forms\Get $get): bool => ! $get('unit_price'))
                        ->dehydrated(fn (string $operation) => $operation === 'create')
                        ->afterStateUpdated(function (Forms\Components\Component $component, Forms\Get $get, Forms\Set $set, $state) {
                            if ($get('unit_price')) {
                                $set('subtotal', round($state * $get('unit_price'), 2));
                                $component->getParentRepeater()->callAfterStateUpdated();
                            }
                        }),
                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()
                        ->prefix('₦')
                        ->disabled()
                        ->minValue(1)
                        ->maxValue(42949672.95)
                        ->required()
                        ->visibleOn('create')
                        ->key('subtotal'),
                ])
                ->key('items'),
            Forms\Components\Grid::make(4)
                ->schema([
                    Forms\Components\Section::make('Summary')
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->collapsed(fn (string $operation) => $operation === 'edit')
                        ->columns(2)
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\TextInput::make('discount')
                                ->numeric()
                                ->suffix('%')
                                ->default(fn () => filament()->auth()->user()->load('setting')->setting->discount)
                                ->minValue(0)
                                ->maxValue(42949672.95)
                                ->live(true)
                                ->afterStateUpdated(function (Forms\Components\Component $component) {
                                    $component
                                        ->getContainer()
                                        ->getParentComponent()
                                        ->getContainer()
                                        ->getParentComponent()
                                        ->getContainer()
                                        ->getComponent('items')
                                        ->callAfterStateUpdated();
                                }),
                            Forms\Components\TextInput::make('vat')
                                ->label('VAT')
                                ->numeric()
                                ->suffix('%')
                                ->default(fn () => filament()->auth()->user()->load('setting')->setting->vat)
                                ->disabled()
                                ->dehydrated()
                                ->minValue(0)
                                ->maxValue(42949672.95)
                                ->required()
                                ->live(true),
                            Forms\Components\TextInput::make('shipping')
                                ->label('Shipping fee')
                                ->numeric()
                                ->prefix('₦')
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(42949672.95)
                                ->live(true)
                                ->afterStateUpdated(function (Forms\Components\Component $component) {
                                    $component
                                        ->getContainer()
                                        ->getParentComponent()
                                        ->getContainer()
                                        ->getParentComponent()
                                        ->getContainer()
                                        ->getComponent('items')
                                        ->callAfterStateUpdated();
                                }),
                            Forms\Components\TextInput::make('tendered')
                                ->numeric()
                                ->prefix('₦')
                                ->default(fn (Forms\Get $get) => $get('total_price'))
                                ->minValue(0)
                                ->maxValue(42949672.95)
                                ->required()
                                ->live(true)
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                    $set('change', round($state - $get('total_price')));

                                    if ($state >= $get('total_price')) {
                                        $set('payment_status', \App\PaymentStatus::Paid->value);
                                    } else {
                                        $set('payment_status', \App\PaymentStatus::Pending->value);
                                    }
                                }),
                            Forms\Components\TextInput::make('total_price')
                                ->numeric()
                                ->prefix('₦')
                                ->disabled()
                                ->dehydrated()
                                ->minValue(1)
                                ->maxValue(42949672.95)
                                ->required(),
                            Forms\Components\TextInput::make('change')
                                ->numeric()
                                ->prefix('₦')
                                ->disabled()
                                ->maxValue(42949672.95)
                                ->required(fn (string $operation) => $operation === 'create'),
                        ]),
                    Forms\Components\Section::make('Payment Info')
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            Forms\Components\Select::make('payment_method')
                                ->options(\App\PaymentMethod::class)
                                ->searchable()
                                ->required(),
                            Forms\Components\DatePicker::make('document.due_date')
                                ->default(now()->toDateTimeString())
                                ->minDate(now()->toDateString())
                                ->disabled(fn (Forms\Get $get): bool => $get('payment_status') === \App\PaymentStatus::Paid->value)
                                ->dehydrated(fn (Forms\Get $get): bool => $get('payment_status') === \App\PaymentStatus::Pending->value)
                                ->required(fn (string $operation) => $operation === 'create'),
                            Forms\Components\Select::make('payment_status')
                                ->options(\App\PaymentStatus::class)
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('notes')
                                ->placeholder('Additional order info...')
                                ->rows(1)
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ]),
                ]),
        ];

        return $base;
    }

    public static function getTable(): array
    {
        return [
            Tables\Columns\TextColumn::make('uuid')
                ->label('Sale ID')
                ->color(Color::Neutral)
                ->searchable(),
            Tables\Columns\TextColumn::make('client.name')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('products_count')->counts('products')
                ->label('Products sold')
                ->sortable()
                ->summarize(Summarizers\Sum::make()->numeric()->prefix('Products: ')),
            Tables\Columns\TextColumn::make('shipping')
                ->money('NGN')
                ->color('success')
                ->sortable(),
            Tables\Columns\TextColumn::make('discount')
                ->color('danger')
                ->suffix('%')
                ->sortable(),
            Tables\Columns\TextColumn::make('vat')
                ->label('VAT')
                ->color('warning')
                ->suffix('%')
                ->sortable(),
            Tables\Columns\TextColumn::make('total_price')
                ->money('NGN')
                ->color('success')
                ->sortable()
                ->summarize(Summarizers\Summarizer::make()
                    ->money('NGN')
                    ->prefix('Total: ')
                    ->using(fn (QueryBuilder $query): float => $query->sum('total_price') / 100)),
            Tables\Columns\TextColumn::make('tendered')
                ->money('NGN')
                ->color('info')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Date')
                ->sortable()
                ->date()
                ->summarize(Summarizers\Count::make()->numeric()->prefix('Sales: ')),
            Tables\Columns\IconColumn::make('payment_status')
                ->icon(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getIcon())
                ->color(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getColor())
                ->tooltip(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getLabel()),
            Tables\Columns\TextColumn::make('cashier.name')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
