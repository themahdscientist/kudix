<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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
                                    ->default(false),
                                Forms\Components\TextInput::make('loyalty_points')
                                    ->numeric()
                                    ->maxValue(42949672.95)
                                    ->disabled()
                                    ->dehydrated(),
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
                Forms\Components\Select::make('payment_method')
                    ->options(\App\PaymentMethod::class)
                    ->required()
                    ->live(true)
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('payment_status', null)),
                Forms\Components\Repeater::make('productSales')
                    ->label('Sale order')
                    ->relationship()
                    ->addActionLabel('Add to cart')
                    ->addable(fn (string $operation): bool => $operation === 'create')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(false)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('unit_cost', Product::query()->find($state, 'price')->price ?? null);
                                $set('quantity', null);
                                $set('total_cost', null);
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
                            ->dehydrated(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Forms\Get $get): int => Product::query()->find($get('product_id'), 'quantity')->quantity)
                            ->validationMessages(['max' => 'The quantity has exceeded the available stock.'])
                            ->live(true)
                            ->disabled(fn (Forms\Get $get): bool => ! $get('unit_cost'))
                            ->afterStateUpdated(function (Forms\Components\TextInput $component, Forms\Get $get, Forms\Set $set, $state) {
                                if ($get('unit_cost')) {
                                    $set('total_cost', $state * $get('unit_cost'));

                                    $component->getContainer()->getComponent('atomic_total_cost')->callAfterStateUpdated();
                                }
                            }),
                        Forms\Components\TextInput::make('total_cost')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->required()
                            ->afterStateUpdated(function (Forms\Components\TextInput $component, Forms\Set $set) {
                                $total = 0;
                                foreach ($component->getContainer()->getParentComponent()->getState() as $parent) {
                                    $total += $parent['total_cost'];
                                }

                                $set('../../total_cost', $total);
                            })
                            ->visibleOn('create')
                            ->key('atomic_total_cost'),
                    ])
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => Product::query()->find($state['product_id'], 'name')->name ?? 'Cart Item')
                    ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation())
                    ->columnSpanFull()
                    ->columns(['sm' => 2, 'md' => 4]),
                Forms\Components\Select::make('payment_status')
                    ->options(function (Forms\Get $get) {
                        if ($get('payment_method') === \App\PaymentMethod::Cash->value) {
                            return [
                                \App\SalePaymentStatus::Paid->value => \App\SalePaymentStatus::Paid->name,
                            ];
                        }

                        return [
                            \App\SalePaymentStatus::Pending->value => \App\SalePaymentStatus::Pending->name,
                            \App\SalePaymentStatus::Refunded->value => \App\SalePaymentStatus::Refunded->name,
                        ];
                    })
                    ->required(),
                Forms\Components\TextInput::make('total_cost')
                    ->numeric()
                    ->prefix('₦')
                    ->disabled()
                    ->dehydrated()
                    ->minValue(1)
                    ->maxValue(42949672.95)
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->placeholder('Additional order info...')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')
                    ->label('Products sold')
                    ->sortable()
                    ->summarize(Summarizers\Sum::make()->numeric()->prefix('Products: ')),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('NGN')
                    ->sortable()
                    ->summarize(Summarizers\Summarizer::make()
                        ->money('NGN')
                        ->prefix('Total: ')
                        ->using(fn (QueryBuilder $query): int => $query->sum('total_cost') / 100)),
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
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // 
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
            ]);
    }
}
