<?php

namespace App\Filament\Admin\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->multiple()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
