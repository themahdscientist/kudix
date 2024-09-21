<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ProductResource\Pages;
use App\Filament\App\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('category')
                ->sortable(),
            Tables\Columns\TextColumn::make('price')
                ->money('NGN')
                ->sortable(),
            Tables\Columns\TextColumn::make('quantity')
                ->label('Stock count')
                ->sortable()
                ->summarize(Summarizers\Sum::make()->numeric()->prefix('Total stock: ')->suffix(' units')),
            Tables\Columns\TextColumn::make('description')
                ->searchable(),
            Tables\Columns\TextColumn::make('sku')
                ->label('SKU')
                ->searchable(),
            Tables\Columns\TextColumn::make('expiry_date')
                ->date()
                ->sortable(),
            Tables\Columns\IconColumn::make('status')
                ->icon(fn ($record): string => \App\ProductStatus::from($record->status)->getIcon())
                ->color(fn ($record): string => \App\ProductStatus::from($record->status)->getColor())
                ->tooltip(fn ($record): string => \App\ProductStatus::from($record->status)->getLabel()),
        ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\ProductStatus::class),
            ])
            ->actions([
                // 
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
