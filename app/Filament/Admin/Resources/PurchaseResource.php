<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
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
                                Forms\Components\Select::make('products')
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
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
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
                                Forms\Components\Select::make('products')
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
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->default(\App\Utils::generatePurchaseId())
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('order_status')
                    ->options(\App\PurchaseOrderStatus::class)
                    ->required(),
                Forms\Components\Repeater::make('productPurchases')
                    ->label('Purchase order')
                    ->relationship()
                    ->addActionLabel('Add to order')
                    ->addable(fn (string $operation): bool => $operation === 'create')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(false)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(fn (Forms\Get $get) => $get('../../supplier_id') ? Supplier::query()->find($get('../../supplier_id'))->load('products')->products->pluck('name', 'id') : null)
                            ->searchable()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('unit_price', Product::query()->find($state, 'price')->price ?? null);
                                $set('quantity', null);
                                $set('total_price', null);
                            }),
                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->live(true)
                            ->disabled(fn (Forms\Get $get): bool => ! $get('unit_price'))
                            ->afterStateUpdated(function (Forms\Components\TextInput $component, Forms\Get $get, Forms\Set $set, $state) {
                                if ($get('unit_price')) {
                                    $set('total_price', $state * $get('unit_price'));

                                    $component->getContainer()->getComponent('atomic_total_price')->callAfterStateUpdated();
                                }
                            }),
                        Forms\Components\TextInput::make('total_price')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->required()
                            ->afterStateUpdated(function (Forms\Components\TextInput $component, Forms\Set $set) {
                                $total = 0;
                                foreach ($component->getContainer()->getParentComponent()->getState() as $parent) {
                                    $total += $parent['total_price'];
                                }

                                $set('../../total_price', $total);
                            })
                            ->visibleOn('create')
                            ->key('atomic_total_price'),
                    ])
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => Product::query()->find($state['product_id'], 'name')->name ?? 'Cart Item')
                    ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation())
                    ->columnSpanFull()
                    ->columns(['sm' => 2, 'md' => 4]),
                Forms\Components\Select::make('payment_status')
                    ->options(\App\PurchasePaymentStatus::class)
                    ->required(),
                Forms\Components\TextInput::make('total_price')
                    ->numeric()
                    ->prefix('₦')
                    ->disabled()
                    ->dehydrated()
                    ->minValue(1)
                    ->maxValue(42949672.95)
                    ->required(),
                Forms\Components\DatePicker::make('expected_delivery_date')
                    ->default(now()->toDateString())
                    ->minDate(now()->toDateString())
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->required(),
                Forms\Components\DatePicker::make('received_date')
                    ->default(now()->toDateString())
                    ->minDate(now()->toDateString()),
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
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')
                    ->label('Products bought')
                    ->sortable()
                    ->summarize(Summarizers\Sum::make()->numeric()->prefix('Products: ')),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('NGN')
                    ->sortable()
                    ->summarize(Summarizers\Summarizer::make()
                        ->money('NGN')
                        ->prefix('Total: ')
                        ->using(fn (QueryBuilder $query): int => $query->sum('total_price') / 100)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order date')
                    ->sortable()
                    ->date()
                    ->summarize(Summarizers\Count::make()->numeric()->prefix('Purchases: ')),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->sortable()
                    ->date(),
                Tables\Columns\TextColumn::make('received_date')
                    ->sortable()
                    ->date(),
                Tables\Columns\IconColumn::make('payment_status')
                    ->icon(fn ($record): string => \App\PurchasePaymentStatus::from($record->payment_status)->getIcon())
                    ->color(fn ($record): string => \App\PurchasePaymentStatus::from($record->payment_status)->getColor())
                    ->tooltip(fn ($record): string => \App\PurchasePaymentStatus::from($record->payment_status)->getLabel()),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(\App\PurchasePaymentStatus::class),
            ])
            ->actions([
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
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
