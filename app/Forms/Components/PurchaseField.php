<?php

namespace App\Forms\Components;

use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms;

class PurchaseField extends Forms\Components\Field
{
    public static function getForm(): array
    {
        $base = [
            Forms\Components\Section::make([
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->createOptionForm(SupplierField::getForm(products: true))
                    ->editOptionForm(SupplierField::getForm(products: true)),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('regenerate')
                            ->icon('heroicon-o-arrow-path')
                            ->iconButton()
                            ->action(fn (Forms\Components\TextInput $component) => $component->state(\App\Utils::generatePurchaseId()))
                    )
                    ->default(\App\Utils::generatePurchaseId())
                    ->disabled()
                    ->dehydrated(fn (string $operation) => $operation === 'create')
                    ->required()
                    ->unique(ignoreRecord: true),
            ])
                ->columns(),
            Forms\Components\Repeater::make('productPurchases')
                ->label('Purchase order')
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
                    $set('total_cost', $total);
                    $set('tendered', $total);
                    $set('change', round($get('tendered') - $total));
                    $set('payment_status', \App\PaymentStatus::Paid->value);
                    $set('order_status', \App\OrderStatus::Pending->value);
                })
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->options(fn (Forms\Get $get) => $get('../../supplier_id') ? Supplier::query()->find($get('../../supplier_id'))->load('products')->products->pluck('name', 'id') : null)
                        ->searchable()
                        ->required()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            $set('unit_cost', round(Product::query()->find($state, 'price')?->price, 2) ?? null);
                            $set('quantity', null);
                            $set('subtotal', null);
                            $set('../../tendered', 0);
                            $set('../../total_cost', null);
                        }),
                    Forms\Components\TextInput::make('unit_cost')
                        ->numeric()
                        ->prefix('₦')
                        ->minValue(1)
                        ->maxValue(42949672.95)
                        ->disabled()
                        ->dehydrated(fn (string $operation) => $operation === 'create'),
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->minValue(0.01)
                        ->maxValue(42949672.95)
                        ->live(true)
                        ->disabled(fn (Forms\Get $get): bool => ! $get('unit_cost'))
                        ->dehydrated(fn (string $operation) => $operation === 'create')
                        ->afterStateUpdated(function (Forms\Components\TextInput $component, Forms\Get $get, Forms\Set $set, $state) {
                            if ($get('unit_cost')) {
                                $set('subtotal', round($state * $get('unit_cost'), 2));
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
                                ->default(fn (Forms\Get $get) => $get('total_cost'))
                                ->minValue(0)
                                ->maxValue(42949672.95)
                                ->required()
                                ->live(true)
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                    $set('change', round($state - $get('total_cost')));

                                    if ($state >= $get('total_cost')) {
                                        $set('payment_status', \App\PaymentStatus::Paid->value);
                                    } else {
                                        $set('payment_status', \App\PaymentStatus::Pending->value);
                                    }
                                }),
                            Forms\Components\TextInput::make('total_cost')
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
                    Forms\Components\Section::make('Order Info')
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            Forms\Components\Select::make('order_status')
                                ->options(\App\OrderStatus::class)
                                ->searchable()
                                ->live(true)
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated()
                                ->required(),
                            Forms\Components\DatePicker::make('expected_delivery_date')
                                ->default(now()->toDateTimeString())
                                ->minDate(now()->toDateString())
                                ->disabled(fn (Forms\Get $get): bool => $get('order_status') === \App\OrderStatus::Received->value)
                                ->dehydrated()
                                ->required(),
                            Forms\Components\Select::make('payment_status')
                                ->options(\App\PaymentStatus::class)
                                ->disabled()
                                ->dehydrated()
                                ->required(),
                            Forms\Components\DatePicker::make('document.due_date')
                                ->default(now()->toDateTimeString())
                                ->minDate(now()->toDateString())
                                ->disabled(fn (Forms\Get $get): bool => $get('payment_status') === \App\PaymentStatus::Paid->value)
                                ->dehydrated(fn (Forms\Get $get): bool => $get('payment_status') === \App\PaymentStatus::Pending->value)
                                ->required(fn (string $operation) => $operation === 'create'),
                            Forms\Components\Textarea::make('notes')
                                ->placeholder('Additional order info...')
                                ->maxLength(65535)
                                ->rows(1)
                                ->columnSpanFull(),
                        ]),
                ]),
        ];

        return $base;
    }
}
