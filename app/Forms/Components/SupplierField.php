<?php

namespace App\Forms\Components;

use Filament\Forms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class SupplierField extends Forms\Components\Field
{
    public static function getComponent($products = false): array
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
                    PhoneInput::make('phone')
                        ->label('Phone number')
                        ->prefixIcon('heroicon-s-phone')
                        ->defaultCountry('NG')
                        ->autoPlaceholder('aggressive')
                        ->ipLookup(function () {
                            return rescue(
                                fn () => Http::get('https://ipinfo.io', ['token' => env('IPINFO_SECRET')])->json('country'),
                                'NG',
                                false
                            );
                        })
                        ->strictMode()
                        ->required(),
                ]),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('address')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->options(\App\SupplierType::class)
                        ->required(),
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
                ]),
            ])
                ->columnSpanFull(),
            Forms\Components\Textarea::make('notes')
                ->maxLength(65535)
                ->rows(1)
                ->columnSpanFull(),
        ];

        if ($products) {
            $base[] = Forms\Components\Select::make('products')
                ->relationship(titleAttribute: 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->columnSpanFull()
                ->createOptionForm(ProductField::getComponent());
        }

        return $base;
    }
}
