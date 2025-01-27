<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CashierResource\Pages;
use App\Filament\Admin\Resources\CashierResource\RelationManagers;
use App\Layouts\CashierLayout;
use App\Models\Cashier;
use App\Models\Role;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class CashierResource extends Resource
{
    protected static ?string $model = Cashier::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $activeNavigationIcon = 'heroicon-s-users';

    protected static ?string $navigationGroup = 'Human Resources';

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(CashierLayout::getForm());
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
                Tables\Columns\TextColumn::make('sales_count')->counts('sales')
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
            RelationManagers\SalesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashiers::route('/'),
            'create' => Pages\CreateCashier::route('/create'),
            'edit' => Pages\EditCashier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('role_id', Role::CASHIER);
    }
}
