<?php

namespace App\Layouts;

use Filament\Forms;
use Illuminate\Support\Str;

abstract class ProductLayout
{
    public static function getForm($suppliers = false): array
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
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
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
                        ->default(\App\Enums\ProductStatus::OutOfStock->value)
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
                ->createOptionForm(SupplierLayout::getForm())
                ->visibleOn('create');
        }

        return $base;
    }
}
