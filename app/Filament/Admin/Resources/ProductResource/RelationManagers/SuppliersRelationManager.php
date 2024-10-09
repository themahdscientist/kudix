<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class SuppliersRelationManager extends RelationManager
{
    protected static string $relationship = 'suppliers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                            ->initialCountry('ng')
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
                            ->prefix('http://')
                            ->prefixIcon('heroicon-s-globe-alt')
                            ->formatStateUsing(fn (mixed $state) => Str::replaceStart('http://', '', $state))
                            ->dehydrateStateUsing(fn (mixed $state) => 'http://'.$state),
                    ]),
                ])
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->rows(1)
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
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\IconColumn::make('type')
                    ->icon(fn ($record) => \App\SupplierType::from($record->type)->getIcon())
                    ->color(fn ($record) => \App\SupplierType::from($record->type)->getColor())
                    ->tooltip(fn ($record) => \App\SupplierType::from($record->type)->getLabel()),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(\App\SupplierType::class),
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
