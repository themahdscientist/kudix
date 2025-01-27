<?php

namespace App\Layouts;

use Filament\Forms;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

abstract class SupplierLayout
{
    public static function getForm($products = false): array
    {
        $base = [
            Forms\Components\Split::make([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Grid::make()
                        ->schema([
                            PhoneInput::make('phone')
                                ->label('Phone number')
                                ->prefixIcon('heroicon-s-phone')
                                ->defaultCountry('NG')
                                ->autoPlaceholder('aggressive')
                                ->strictMode()
                                ->required(),
                            Forms\Components\Select::make('type')
                                ->options(\App\Enums\SupplierType::class)
                                ->searchable()
                                ->required(),
                        ]),
                ]),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('address')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('website')
                        ->prefix('http(s)://')
                        ->live(true)
                        ->prefixIcon('heroicon-s-globe-alt')
                        ->formatStateUsing(fn (mixed $state) => Str::replaceStart('http://', '', $state))
                        ->dehydrateStateUsing(function (mixed $state) {
                            if (Str::startsWith($state, 'https://') || Str::startsWith($state, 'http://')) {
                                return $state;
                            }

                            return 'http://'.$state;
                        })
                        ->afterStateUpdated(function (Forms\Components\TextInput $component, mixed $state) {
                            Str::startsWith($state, 'https://') ?
                            $component->state(Str::replaceStart('https://', '', $state)) :
                            $component->state(Str::replaceStart('http://', '', $state));
                        }),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(65535)
                        ->rows(1),
                ]),
            ])
                ->columnSpanFull(),
        ];

        if ($products) {
            $base[] = Forms\Components\Select::make('products')
                ->relationship(titleAttribute: 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->columnSpanFull()
                ->createOptionForm(ProductLayout::getForm())
                ->visibleOn('create');
        }

        return $base;
    }
}
