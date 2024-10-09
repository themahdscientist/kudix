<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseResource\Pages;
use App\Forms\Components\SupplierField;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->createOptionForm(SupplierField::getComponent(products: true))
                        ->editOptionForm(SupplierField::getComponent(products: true)),
                    Forms\Components\TextInput::make('uuid')
                        ->label('UUID')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('regenerate')
                                ->icon('heroicon-o-arrow-path')
                                ->iconButton()
                                ->action(fn (Forms\Components\TextInput $component) => $component->state(\App\Utils::generatePurchaseId()))
                        )
                        ->default(\App\Utils::generatePurchaseId())
                        ->disabled()
                        ->dehydrated(fn (string $operation) => $operation === 'create')
                        ->required()
                        ->unique(ignoreRecord: true),
                ])
                    ->columns(),
                Forms\Components\Repeater::make('productPurchases')
                    ->label('Purchase order')
                    ->relationship()
                    ->addActionLabel('Add to cart')
                    ->addable(fn (string $operation): bool => $operation === 'create')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(false)
                    ->collapsed(fn (string $operation) => $operation === 'edit')
                    ->itemLabel(fn (array $state): ?string => Product::query()->find($state['product_id'], 'name')->name ?? 'Cart Item')
                    ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation())
                    ->columnSpanFull()
                    ->columns(\App\Utils::responsive())
                    ->live(true)
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                        $subtotal = 0;
                        foreach ($state as $item) {
                            $subtotal += $item['subtotal'];
                        }

                        $discount = (float) $get('discount') * $subtotal / 100;
                        $vat = (float) $get('vat') / 100;
                        $shipping = (float) $get('shipping');

                        $total = round(($subtotal - $discount) * (1 + $vat) + $shipping, 2);
                        $set('total_cost', $total);
                        $set('tendered', $total);
                        $set('change', round($get('tendered') - $total));
                        $set('payment_status', \App\PaymentStatus::Paid->value);
                        $set('order_status', \App\OrderStatus::Pending->value);
                    })
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(fn (Forms\Get $get) => $get('../../supplier_id') ? Supplier::query()->find($get('../../supplier_id'))->load('products')->products->pluck('name', 'id') : null)
                            ->searchable()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('unit_cost', round(Product::query()->find($state, 'price')?->price, 2) ?? null);
                                $set('quantity', null);
                                $set('subtotal', null);
                                $set('../../tendered', 0);
                                $set('../../total_cost', null);
                            }),
                        Forms\Components\TextInput::make('unit_cost')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->disabled()
                            ->dehydrated(fn (string $operation) => $operation === 'create'),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->live(true)
                            ->disabled(fn (Forms\Get $get): bool => ! $get('unit_cost'))
                            ->dehydrated(fn (string $operation) => $operation === 'create')
                            ->afterStateUpdated(function (Forms\Components\TextInput $component, Forms\Get $get, Forms\Set $set, $state) {
                                if ($get('unit_cost')) {
                                    $set('subtotal', round($state * $get('unit_cost'), 2));
                                    $component->getParentRepeater()->callAfterStateUpdated();
                                }
                            }),
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->required()
                            ->visibleOn('create')
                            ->key('subtotal'),
                    ])
                    ->key('items'),
                Forms\Components\Section::make('Summary')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->collapsed(fn (string $operation) => $operation === 'edit')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->suffix('%')
                            ->default(fn () => filament()->auth()->user()->load('setting')->setting->discount)
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->live(true)
                            ->afterStateUpdated(function (Forms\Components\Component $component) {
                                $component
                                    ->getContainer()
                                    ->getParentComponent()
                                    ->getContainer()
                                    ->getComponent('items')
                                    ->callAfterStateUpdated();
                            }),
                        Forms\Components\TextInput::make('shipping')
                            ->label('Shipping fee')
                            ->numeric()
                            ->prefix('₦')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->live(true)
                            ->afterStateUpdated(function (Forms\Components\Component $component) {
                                $component
                                    ->getContainer()
                                    ->getParentComponent()
                                    ->getContainer()
                                    ->getComponent('items')
                                    ->callAfterStateUpdated();
                            }),
                        Forms\Components\TextInput::make('vat')
                            ->label('VAT')
                            ->numeric()
                            ->suffix('%')
                            ->default(fn () => filament()->auth()->user()->load('setting')->setting->vat)
                            ->disabled()
                            ->dehydrated()
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->required()
                            ->live(true),
                        Forms\Components\TextInput::make('tendered')
                            ->numeric()
                            ->prefix('₦')
                            ->default(fn (Forms\Get $get) => $get('total_cost'))
                            ->minValue(0)
                            ->maxValue(42949672.95)
                            ->required()
                            ->live(true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                $set('change', round($state - $get('total_cost')));

                                if ($state >= $get('total_cost')) {
                                    $set('payment_status', \App\PaymentStatus::Paid->value);
                                } else {
                                    $set('payment_status', \App\PaymentStatus::Pending->value);
                                }
                            }),
                        Forms\Components\TextInput::make('total_cost')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->dehydrated()
                            ->minValue(1)
                            ->maxValue(42949672.95)
                            ->required(),
                        Forms\Components\TextInput::make('change')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->maxValue(42949672.95)
                            ->required(fn (string $operation) => $operation === 'create'),
                    ]),
                Forms\Components\Section::make([
                    Forms\Components\Select::make('order_status')
                        ->options(\App\OrderStatus::class)
                        ->searchable()
                        ->live(true)
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->required(),
                    Forms\Components\DatePicker::make('expected_delivery_date')
                        ->default(now()->toDateTimeString())
                        ->minDate(now()->toDateString())
                        ->disabled(fn (Forms\Get $get): bool => $get('order_status') === \App\OrderStatus::Received->value)
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Select::make('payment_status')
                        ->options(\App\PaymentStatus::class)
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\DatePicker::make('document.due_date')
                        ->default(now()->toDateTimeString())
                        ->minDate(now()->toDateString())
                        ->disabled(fn (Forms\Get $get): bool => $get('payment_status') === \App\PaymentStatus::Paid->value)
                        ->dehydrated(fn (Forms\Get $get): bool => $get('payment_status') === \App\PaymentStatus::Pending->value)
                        ->required(fn (string $operation) => $operation === 'create'),
                    Forms\Components\Textarea::make('notes')
                        ->placeholder('Additional order info...')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                    ->columns(4),
            ]);
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
                    ->icon(fn ($record): string => \App\OrderStatus::from($record->order_status)->getIcon())
                    ->color(fn ($record): string => \App\OrderStatus::from($record->order_status)->getColor())
                    ->tooltip(fn ($record): string => \App\OrderStatus::from($record->order_status)->getLabel()),
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
                    ->icon(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getIcon())
                    ->color(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getColor())
                    ->tooltip(fn ($record): string => \App\PaymentStatus::from($record->payment_status)->getLabel()),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->label('Expected delivery date')
                    ->sortable()
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('order_status')
                    ->options(\App\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(\App\PaymentStatus::class),
            ])
            ->actions([
                Tables\Actions\Action::make('receive')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-s-archive-box-arrow-down')
                    ->modalSubmitActionLabel('Receive')
                    ->icon('heroicon-s-archive-box-arrow-down')
                    ->iconButton()
                    ->hidden(fn (Purchase $record) => $record->trashed())
                    ->action(function (Purchase $record) {
                        $record->update(['order_status' => \App\OrderStatus::Received->value]);

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
                    ->hidden(fn (Purchase $record) => $record->tendered >= $record->total_cost || $record->payment_status === \App\PaymentStatus::Paid->value || $record->trashed())
                    ->url(fn (Purchase $record) => DocumentResource::getUrl('view', [$record->document])),
                Tables\Actions\Action::make('receipt')
                    ->icon('heroicon-s-receipt-percent')
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn (Purchase $record) => $record->tendered < $record->total_cost || $record->payment_status !== \App\PaymentStatus::Paid->value || $record->trashed())
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
            ->with('document');
    }
}
