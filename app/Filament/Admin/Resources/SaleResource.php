<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SaleResource\Pages;
use App\Forms\Components\ClientField;
use App\Models\Client;
use App\Models\Product;
use App\Models\Role;
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

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $activeNavigationIcon = 'heroicon-s-tag';

    protected static ?string $navigationGroup = 'Business Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'name', fn (Builder $query) => $query->where('role_id', Role::CLIENT))
                        ->searchable()
                        ->preload()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->manageOptionForm(ClientField::getComponent())
                        ->fillEditOptionActionFormUsing(function (mixed $state) {
                            return Client::query()->find($state)->load('clientInfo')->toArray();
                        })
                        ->updateOptionUsing(function (array $data, Form $form) {
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
                Forms\Components\Section::make('Summary')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->collapsed(fn (string $operation) => $operation === 'edit')
                    ->columns(3)
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
                            ->default(fn () => filament()->auth()->user()->load('setting')->setting->vat)
                            ->disabled()
                            ->dehydrated()
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->required()
                            ->live(true),
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
                Forms\Components\Section::make([
                    Forms\Components\Select::make('payment_method')
                        ->options(\App\PaymentMethod::class)
                        ->searchable()
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
                        ->columnSpanFull(),
                ])
                    ->columns(3),
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
                    ->hidden(fn (Sale $record) => $record->tendered >= $record->total_price || $record->payment_status === \App\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Sale $record) => DocumentResource::getUrl('view', [$record->document])),
                Tables\Actions\Action::make('receipt')
                    ->icon('heroicon-s-receipt-percent')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Sale $record) => $record->tendered < $record->total_price || $record->payment_status !== \App\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Sale $record) => DocumentResource::getUrl('view', [$record->document])),
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
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with('document');
    }
}
