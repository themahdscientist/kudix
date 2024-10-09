<?php

namespace App\Forms\Components;

use Filament\Forms;
use Illuminate\Support\Str;

class ProductField extends Forms\Components\Field
{
    public static function getComponent($suppliers = false): array
    {
        $base = [
            Forms\Components\Split::make([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(true)
                        ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('sku', Str::slug($state))),
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

                ]),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('description')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->prefix('₦')
                        ->dehydrateStateUsing(fn (float $state) => round($state, 2))
                        ->maxValue(42949672.95)
                        ->required(),
                    Forms\Components\TextInput::make('status')
                        ->default(\App\ProductStatus::OutOfStock->value)
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Textarea::make('dosage')
                        ->maxLength(65535)
                        ->rows(1),
                ]),
            ])
                ->columnSpanFull(),
        ];

        if ($suppliers) {
            $base[] = Forms\Components\Select::make('suppliers')
                ->relationship(titleAttribute: 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->columnSpanFull()
                ->createOptionForm(SupplierField::getComponent());
        }

        return $base;
    }
}
