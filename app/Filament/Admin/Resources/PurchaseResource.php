<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseResource\Pages;
use App\Layouts\PurchaseLayout;
use App\Models\ProductPurchase;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $activeNavigationIcon = 'heroicon-s-shopping-bag';

    protected static ?string $navigationGroup = 'Business Operations';

    protected static ?string $navigationBadgeTooltip = 'Today\'s purchases.';

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
            ->schema(PurchaseLayout::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Purchase ID')
                    ->color(Color::Neutral)
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('products_count')->counts('products')
                    ->label('Products bought')
                    ->sortable()
                    ->summarize(Summarizers\Sum::make()->numeric()->prefix('Products: ')),
                Tables\Columns\IconColumn::make('order_status')
                    ->icon(fn ($record): string => \App\Enums\OrderStatus::from($record->order_status)->getIcon())
                    ->color(fn ($record): string => \App\Enums\OrderStatus::from($record->order_status)->getColor())
                    ->tooltip(fn ($record): string => \App\Enums\OrderStatus::from($record->order_status)->getLabel()),
                Tables\Columns\TextColumn::make('shipping')
                    ->money('NGN')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->color('danger')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vat')
                    ->label('VAT')
                    ->color('warning')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('NGN')
                    ->color('success')
                    ->sortable()
                    ->summarize(Summarizers\Summarizer::make()
                        ->money('NGN')
                        ->prefix('Total: ')
                        ->using(fn (QueryBuilder $query): float => $query->sum('total_cost') / 100)),
                Tables\Columns\TextColumn::make('tendered')
                    ->money('NGN')
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->sortable()
                    ->date()
                    ->summarize(Summarizers\Count::make()->numeric()->prefix('Purchases: ')),
                Tables\Columns\IconColumn::make('payment_status')
                    ->icon(fn ($record): string => \App\Enums\PaymentStatus::from($record->payment_status)->getIcon())
                    ->color(fn ($record): string => \App\Enums\PaymentStatus::from($record->payment_status)->getColor())
                    ->tooltip(fn ($record): string => \App\Enums\PaymentStatus::from($record->payment_status)->getLabel()),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->label('Expected delivery date')
                    ->sortable()
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('order_status')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(\App\Enums\PaymentStatus::class),
            ])
            ->actions([
                Tables\Actions\Action::make('receive')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-s-archive-box-arrow-down')
                    ->modalWidth(MaxWidth::ScreenSmall)
                    ->modalSubmitActionLabel('Receive')
                    ->icon('heroicon-s-archive-box-arrow-down')
                    ->iconButton()
                    ->disabled(fn (Purchase $record) => $record->trashed() || $record->order_status == \App\Enums\OrderStatus::Received->value)
                    ->form(function (Purchase $record): array {
                        return $record->productPurchases->map(function (ProductPurchase $productPurchase) {
                            return Forms\Components\Repeater::make($productPurchase->id)
                                ->label('')
                                ->columns(3)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->itemLabel(fn (): string => $productPurchase->product->name)
                                ->collapsible()
                                ->dehydrated(fn (): bool => $productPurchase->quantity > $productPurchase->received_quantity)
                                ->schema([
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->default($productPurchase->quantity)
                                        ->disabled(),
                                    Forms\Components\TextInput::make('received_quantity')
                                        ->label('Received')
                                        ->numeric()
                                        ->default($productPurchase->received_quantity ?? 0)
                                        ->disabled(),
                                    Forms\Components\TextInput::make('receive_quantity')
                                        ->label('Receive')
                                        ->numeric()
                                        ->disabled(fn (): bool => $productPurchase->quantity <= $productPurchase->received_quantity)
                                        ->minValue(fn (): int => $productPurchase->quantity > $productPurchase->received_quantity)
                                        ->maxValue($productPurchase->quantity - $productPurchase->received_quantity)
                                        ->default($productPurchase->quantity - $productPurchase->received_quantity),
                                ]);
                        })
                            ->toArray();
                    })
                    ->action(function (mixed $data) {
                        foreach ($data as $id => $arr) {
                            $productPurchase = ProductPurchase::query()->find($id)->load('purchase.productPurchases');

                            if ($productPurchase->received_quantity < $productPurchase->quantity) {
                                $productPurchase->update([
                                    'last_receive_quantity' => $arr[0]['receive_quantity'],
                                    'received_quantity' => $productPurchase->received_quantity + $arr[0]['receive_quantity'],
                                ]);
                            }

                            if ($productPurchase->received_quantity > $productPurchase->quantity) {
                                $productPurchase->update(['received_quantity' => $productPurchase->quantity]);
                            }

                            $fullyReceived = $productPurchase->purchase->productPurchases->fresh()->every(function (ProductPurchase $productPurchase) {
                                return $productPurchase->received_quantity == $productPurchase->quantity;
                            });

                            if ($fullyReceived) {
                                $productPurchase->purchase()->update(['order_status' => \App\Enums\OrderStatus::Received->value]);
                            }
                        }

                        Notification::make('received')
                            ->title('Received')
                            ->body('The products have been received and synced into the inventory.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('invoice')
                    ->icon('heroicon-s-document-text')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Purchase $record) => $record->tendered >= $record->total_cost || $record->payment_status === \App\Enums\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Purchase $record) => DocumentResource::getUrl('view', [$record->document])),
                Tables\Actions\Action::make('receipt')
                    ->icon('heroicon-s-receipt-percent')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Purchase $record) => $record->tendered < $record->total_cost || $record->payment_status !== \App\Enums\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Purchase $record) => DocumentResource::getUrl('view', [$record->document])),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with('document', 'productPurchases.product');
    }
}
