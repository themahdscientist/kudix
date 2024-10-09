<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Filament\Admin\Resources\ProductResource\Widgets;
use App\Forms\Components\ProductField;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $activeNavigationIcon = 'heroicon-s-cube';

    protected static ?string $navigationBadgeTooltip = 'The number of viable products (in stock and not expired).';

    public static function getViableProduct(): ?string
    {
        return static::getEloquentQuery()->where(function (Builder $query) {
            $query->whereNot('status', \App\ProductStatus::OutOfStock->value)
                ->whereDate('expiry_date', '>', now());
        })
            ->count();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getViableProduct();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getViableProduct() < 10 ? 'danger' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ProductField::getComponent(suppliers: true));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Stock count')
                    ->sortable()
                    ->summarize(Summarizers\Sum::make()->numeric()->prefix('Total stock: ')->suffix(' units')),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->icon(fn ($record): string => \App\ProductStatus::from($record->status)->getIcon())
                    ->color(fn ($record): string => \App\ProductStatus::from($record->status)->getColor())
                    ->tooltip(fn ($record): string => \App\ProductStatus::from($record->status)->getLabel()),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\ProductStatus::class),
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
            RelationManagers\SuppliersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ExpiredProducts::class,
        ];
    }
}
