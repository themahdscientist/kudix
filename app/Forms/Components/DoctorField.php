<?php

namespace App\Forms\Components;

use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use RalphJSmit\Filament\Components\Forms\Sidebar;
use RalphJSmit\Filament\Components\Forms\Timestamp;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class DoctorField extends Forms\Components\Field
{
    public static function getForm(): array
    {
        $base = [
            Sidebar::make([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->autofocus(),
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
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required(fn (string $operation) => $operation === 'create')
                        ->rule(Password::default())
                        ->dehydrated(fn (mixed $state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                ])
                    ->columns(),
            ], [
                Forms\Components\Section::make([
                    Timestamp::make('created_at', 'Employed'),
                    Timestamp::make('deleted_at', 'Laid off'),
                ]),
            ]),
        ];

        return $base;
    }
}
