<?php

namespace App\Forms\Components;

use App\Models\Role;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use RalphJSmit\Filament\Components\Forms\Sidebar;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ClientField extends Forms\Components\Field
{
    public static function getComponent(): array
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
                    Forms\Components\TextInput::make('address')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
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
                    Forms\Components\Select::make('client_info.loyalty_program_id')
                        ->label('Loyalty program')
                        ->relationship('clientInfo.loyaltyProgram', 'name')
                        ->searchable()
                        ->preload()
                        ->dehydrated(fn ($state) => filled($state)),
                    Forms\Components\Select::make('client_info.type')
                        ->options(\App\ClientType::class)
                        ->searchable()
                        ->live(true)
                        ->required(),
                    Forms\Components\Select::make('client_info.doctor_id')
                        ->label('Doctor')
                        ->relationship('clientInfo.doctor', 'name', fn (Builder $query) => $query->where('role_id', Role::DOCTOR))
                        ->searchable()
                        ->preload()
                        ->requiredIf('client_info.type', \App\ClientType::Patient->value)
                        ->visible(fn (Forms\Get $get) => $get('client_info.type') == \App\ClientType::Patient->value),
                ]),
            ]),
        ];

        return $base;
    }
}
