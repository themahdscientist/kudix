<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use RalphJSmit\Filament\Components\Forms\Sidebar;
use RalphJSmit\Filament\Components\Forms\Timestamp;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $activeNavigationIcon = 'heroicon-s-beaker';

    protected static ?string $navigationGroup = 'Human Resources';

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                PhoneColumn::make('phone'),
                Tables\Columns\TextColumn::make('patients_count')->counts('patients')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date employed')
                    ->dateTime()
                    ->sinceTooltip()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('role_id', Role::DOCTOR);
    }
}
