<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SupplierResource\Pages;
use App\Filament\Admin\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
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
                    ->visibleOn('create')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\IconColumn::make('type')
                    ->icon(fn ($record) => \App\SupplierType::from($record->type)->getIcon())
                    ->color(fn ($record) => \App\SupplierType::from($record->type)->getColor())
                    ->tooltip(fn ($record) => \App\SupplierType::from($record->type)->getLabel()),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(\App\SupplierType::class),
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
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
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
