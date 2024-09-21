<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->createOptionForm([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone number')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextInput::make('address')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Checkbox::make('loyalty_program_member')
                                    ->live()
                                    ->default(false),
                                Forms\Components\TextInput::make('loyalty_points')
                                    ->numeric()
                                    ->maxValue(42949672.95)
                                    ->default(0)
                                    ->disabled(fn (Forms\Get $get) => ! $get('loyalty_program_member')),
                            ]),
                    ])
                    ->editOptionForm([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone number')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextInput::make('address')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Checkbox::make('loyalty_program_member')
                                    ->default(false),
                                Forms\Components\TextInput::make('loyalty_points')
                                    ->numeric()
                                    ->maxValue(42949672.95)
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->default(\App\Utils::generateSaleId())
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(ignoreRecord: true),
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
                        $set('total_cost', $total);
                        $set('tendered', $total);
                        $set('change', round($get('tendered') - $total));
                        $set('payment_status', \App\SalePaymentStatus::Paid->value);
                    })
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('unit_cost', round(Product::query()->find($state, 'price')->price, 2) ?? null);
                                $set('quantity', null);
                                $set('subtotal', null);
                                $set('../../discount', 0);
                                $set('../../shipping', 0);
                                $set('../../tendered', 0);
                                $set('../../total_cost', null);
                            })
                            ->createOptionForm([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(true)
                                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('sku', \Illuminate\Support\Str::slug($state))),
                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->disabled()
                                            ->dehydrated()
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(),
                                        Forms\Components\Select::make('category')
                                            ->options([
                                                'Over-the-Counter (OTC) Medications' => [
                                                    'pain-relievers' => 'Pain relievers',
                                                    'cold-and-flu-remedies' => 'Cold and flu remedies',
                                                    'allergy-medications' => 'Allergy medications',
                                                    'antacids' => 'Antacids',
                                                    'vitamins-and-supplements' => 'Vitamins and supplements',
                                                    'first-aid-supplies' => 'First aid supplies',
                                                ],
                                                'Prescription Medications' => [
                                                    'antibiotics' => 'Antibiotics',
                                                    'antidepressants' => 'Antidepressants',
                                                    'antihypertensives' => 'Antihypertensives',
                                                    'cardiovascular-medications' => 'Cardiovascular medications',
                                                    'diabetes-medications' => 'Diabetes medications',
                                                    'respiratory-medications' => 'Respiratory medications',
                                                    'oncology-medications' => 'Oncology medications',
                                                ],
                                                'Medical Devices' => [
                                                    'blood-pressure-monitors' => 'Blood pressure monitors',
                                                    'glucose-meters' => 'Glucose meters',
                                                    'thermometers' => 'Thermometers',
                                                    'nebulizers' => 'Nebulizers',
                                                    'hearing-aids' => 'Hearing aids',
                                                    'contact-lenses and solutions' => 'Contact lenses and solutions',
                                                ],
                                                'Personal Care Products' => [
                                                    'skincare-products' => 'Skincare products',
                                                    'haircare-products' => 'Haircare products',
                                                    'cosmetics' => 'Cosmetics',
                                                    'oral-hygiene-products' => 'Oral hygiene products',
                                                    'baby-products' => 'Baby products',
                                                ],
                                                'Other Categories' => [
                                                    'homeopathic-remedies' => 'Homeopathic remedies',
                                                    'herbal-supplements' => 'Herbal supplements',
                                                    'veterinary-products' => 'Veterinary products',
                                                    'medical-equipment' => 'Medical equipment',
                                                ],
                                            ])
                                            ->required(),
                                        Forms\Components\DatePicker::make('expiry_date')
                                            ->required()
                                            ->default(now()->toDateString())
                                            ->minDate(now()->toDateString()),
                                        Forms\Components\TextInput::make('description')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('suppliers')
                                            ->relationship(titleAttribute: 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull()
                                            ->createOptionForm([
                                                Forms\Components\Grid::make()
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\TextInput::make('email')
                                                            ->label('Email address')
                                                            ->email()
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->unique(),
                                                        Forms\Components\TextInput::make('phone')
                                                            ->label('Phone number')
                                                            ->tel()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('address')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\Select::make('type')
                                                            ->options(\App\SupplierType::class)
                                                            ->required(),
                                                        Forms\Components\TextInput::make('website')
                                                            ->url()
                                                            ->live(true)
                                                            ->afterStateUpdated(function (Forms\Components\Component $component, Forms\Set $set, $state) {
                                                                if (! str_starts_with($state, 'http')) {
                                                                    $set($component, 'http://'.$state);
                                                                } else {
                                                                    $set($component, $state);
                                                                }
                                                            }),
                                                        Forms\Components\Textarea::make('notes')
                                                            ->maxLength(65535)
                                                            ->columnSpanFull(),
                                                    ]),
                                            ]),
                                        Forms\Components\TextInput::make('price')
                                            ->numeric()
                                            ->prefix('₦')
                                            ->required()
                                            ->maxValue(42949672.95),
                                        Forms\Components\TextInput::make('status')
                                            ->default(\App\ProductStatus::OutOfStock)
                                            ->disabled()
                                            ->dehydrated()
                                            ->required(),
                                        Forms\Components\Textarea::make('dosage')
                                            ->maxLength(65535)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->editOptionForm([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(true)
                                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('sku', \Illuminate\Support\Str::slug($state))),
                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->disabled()
                                            ->dehydrated()
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true),
                                        Forms\Components\Select::make('category')
                                            ->options([
                                                'Over-the-Counter (OTC) Medications' => [
                                                    'pain-relievers' => 'Pain relievers',
                                                    'cold-and-flu-remedies' => 'Cold and flu remedies',
                                                    'allergy-medications' => 'Allergy medications',
                                                    'antacids' => 'Antacids',
                                                    'vitamins-and-supplements' => 'Vitamins and supplements',
                                                    'first-aid-supplies' => 'First aid supplies',
                                                ],
                                                'Prescription Medications' => [
                                                    'antibiotics' => 'Antibiotics',
                                                    'antidepressants' => 'Antidepressants',
                                                    'antihypertensives' => 'Antihypertensives',
                                                    'cardiovascular-medications' => 'Cardiovascular medications',
                                                    'diabetes-medications' => 'Diabetes medications',
                                                    'respiratory-medications' => 'Respiratory medications',
                                                    'oncology-medications' => 'Oncology medications',
                                                ],
                                                'Medical Devices' => [
                                                    'blood-pressure-monitors' => 'Blood pressure monitors',
                                                    'glucose-meters' => 'Glucose meters',
                                                    'thermometers' => 'Thermometers',
                                                    'nebulizers' => 'Nebulizers',
                                                    'hearing-aids' => 'Hearing aids',
                                                    'contact-lenses and solutions' => 'Contact lenses and solutions',
                                                ],
                                                'Personal Care Products' => [
                                                    'skincare-products' => 'Skincare products',
                                                    'haircare-products' => 'Haircare products',
                                                    'cosmetics' => 'Cosmetics',
                                                    'oral-hygiene-products' => 'Oral hygiene products',
                                                    'baby-products' => 'Baby products',
                                                ],
                                                'Other Categories' => [
                                                    'homeopathic-remedies' => 'Homeopathic remedies',
                                                    'herbal-supplements' => 'Herbal supplements',
                                                    'veterinary-products' => 'Veterinary products',
                                                    'medical-equipment' => 'Medical equipment',
                                                ],
                                            ])
                                            ->required(),
                                        Forms\Components\DatePicker::make('expiry_date')
                                            ->required()
                                            ->default(now()->toDateString())
                                            ->minDate(now()->toDateString()),
                                        Forms\Components\TextInput::make('description')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('suppliers')
                                            ->relationship(titleAttribute: 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull()
                                            ->createOptionForm([
                                                Forms\Components\Grid::make()
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\TextInput::make('email')
                                                            ->label('Email address')
                                                            ->email()
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->unique(),
                                                        Forms\Components\TextInput::make('phone')
                                                            ->label('Phone number')
                                                            ->tel()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('address')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\Select::make('type')
                                                            ->options(\App\SupplierType::class)
                                                            ->required(),
                                                        Forms\Components\TextInput::make('website')
                                                            ->url()
                                                            ->live(true)
                                                            ->afterStateUpdated(function (Forms\Components\Component $component, Forms\Set $set, $state) {
                                                                if (! str_starts_with($state, 'http')) {
                                                                    $set($component, 'http://'.$state);
                                                                } else {
                                                                    $set($component, $state);
                                                                }
                                                            }),
                                                        Forms\Components\Textarea::make('notes')
                                                            ->maxLength(65535)
                                                            ->columnSpanFull(),
                                                    ]),
                                            ]),
                                        Forms\Components\TextInput::make('price')
                                            ->numeric()
                                            ->prefix('₦')
                                            ->required()
                                            ->maxValue(42949672.95),
                                        Forms\Components\TextInput::make('status')
                                            ->default(\App\ProductStatus::OutOfStock)
                                            ->disabled()
                                            ->dehydrated()
                                            ->required(),
                                        Forms\Components\Textarea::make('dosage')
                                            ->maxLength(65535)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Forms\Components\TextInput::make('unit_cost')
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
                            ->disabled(fn (Forms\Get $get): bool => ! $get('unit_cost'))
                            ->afterStateUpdated(function (Forms\Components\Component $component, Forms\Get $get, Forms\Set $set, $state) {
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
                Forms\Components\Section::make('Summary')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->collapsed(fn (string $operation) => $operation === 'edit')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->live(true)
                            ->afterStateUpdated(function (Forms\Components\Component $component) {
                                $component
                                    ->getContainer()
                                    ->getParentComponent()
                                    ->getContainer()
                                    ->getComponent('items')
                                    ->callAfterStateUpdated();
                            }),
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
                                    ->getComponent('items')
                                    ->callAfterStateUpdated();
                            }),
                        Forms\Components\TextInput::make('vat')
                            ->label('VAT')
                            ->numeric()
                            ->suffix('%')
                            ->default(5)
                            ->disabled()
                            ->dehydrated()
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->required()
                            ->live(true),
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
                                    $set('payment_status', \App\SalePaymentStatus::Paid->value);
                                } else {
                                    $set('payment_status', \App\SalePaymentStatus::Pending->value);
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
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options(\App\PaymentMethod::class)
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options(\App\SalePaymentStatus::class)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(now()->toDateTimeString())
                            ->minDate(now()->toDateString())
                            ->hidden(fn (Forms\Get $get): bool => $get('payment_status') === \App\SalePaymentStatus::Paid->value)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Additional order info...')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Sale ID')
                    ->color(Color::Neutral)
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
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
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('NGN')
                    ->color('success')
                    ->sortable()
                    ->summarize(Summarizers\Summarizer::make()
                        ->money('NGN')
                        ->prefix('Total: ')
                        ->using(fn (QueryBuilder $query): float => $query->sum('total_cost') / 100)),
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
                    ->icon(fn ($record): string => \App\SalePaymentStatus::from($record->payment_status)->getIcon())
                    ->color(fn ($record): string => \App\SalePaymentStatus::from($record->payment_status)->getColor())
                    ->tooltip(fn ($record): string => \App\SalePaymentStatus::from($record->payment_status)->getLabel()),
                Tables\Columns\TextColumn::make('salesperson.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(\App\PaymentMethod::class),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->icon('heroicon-s-document-text')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Sale $record) => $record->tendered >= $record->total_cost || $record->payment_status === \App\SalePaymentStatus::Paid->value)
                    ->url(fn (Sale $record) => Pages\ViewInvoice::getUrl(compact('record'))),
                Tables\Actions\Action::make('receipt')
                    ->icon('heroicon-s-receipt-percent')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Sale $record) => $record->tendered < $record->total_cost || $record->payment_status !== \App\SalePaymentStatus::Paid->value)
                    // ! you are here
                    ->action(fn ($record) => dd($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
            'invoice' => Pages\ViewInvoice::route('/{record}/invoice'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with('invoice');
    }
}
