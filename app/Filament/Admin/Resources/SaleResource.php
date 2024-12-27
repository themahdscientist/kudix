<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SaleResource\Pages;
use App\Forms\Components\SaleField;
use App\Models\Sale;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $activeNavigationIcon = 'heroicon-s-tag';

    protected static ?string $navigationGroup = 'Business Operations';

    protected static ?string $navigationBadgeTooltip = 'Today\'s sales.';

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->whereDate('created_at', now())->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return (int) static::getNavigationBadge() > 0 ? 'success' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(SaleField::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(SaleField::getTable())
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(\App\PaymentMethod::class),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->icon('heroicon-s-document-text')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Sale $record) => $record->tendered >= $record->total_price || $record->payment_status === \App\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Sale $record) => DocumentResource::getUrl('view', [$record->document])),
                Tables\Actions\Action::make('receipt')
                    ->icon('heroicon-s-receipt-percent')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Sale $record) => $record->tendered < $record->total_price || $record->payment_status !== \App\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Sale $record) => DocumentResource::getUrl('view', [$record->document])),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with('document');
    }
}
