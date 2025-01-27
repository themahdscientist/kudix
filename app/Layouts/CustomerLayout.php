<?php

namespace App\Layouts;

use App\Models\Role;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use RalphJSmit\Filament\Components\Forms\Sidebar;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

abstract class CustomerLayout
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
                        ->defaultCountry(function () {
                            $id = filament()->auth()->id();
                            $country = Cache::get("ip-location-{$id}");

                            if ($country) {
                                return $country;
                            }

                            return 'NG';
                        })
                        ->autoPlaceholder('aggressive')
                        ->strictMode()
                        ->required(),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required(fn (string $operation) => $operation === 'create')
                        ->rule(Password::default())
                        ->dehydrated(fn (mixed $state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                    Forms\Components\TextInput::make('customer_info.address')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                    ->columns(),
            ], [
                Forms\Components\Section::make([
                    Forms\Components\Select::make('customer_info.loyalty_program_id')
                        ->label('Loyalty program')
                        ->relationship('customerInfo.loyaltyProgram', 'name')
                        ->searchable()
                        ->preload()
                        ->dehydrated(fn ($state) => filled($state)),
                    Forms\Components\Select::make('customer_info.type')
                        ->options(\App\Enums\CustomerType::class)
                        ->searchable()
                        ->live(true)
                        ->required(),
                    Forms\Components\Select::make('customer_info.doctor_id')
                        ->label('Doctor')
                        ->relationship('customerInfo.doctor', 'name', fn (Builder $query) => $query->where('role_id', Role::DOCTOR))
                        ->searchable()
                        ->preload()
                        ->requiredIf('customer_info.type', \App\Enums\CustomerType::Patient->value)
                        ->visible(fn (Forms\Get $get) => $get('customer_info.type') == \App\Enums\CustomerType::Patient->value),
                ]),
            ]),
        ];

        return $base;
    }
}
