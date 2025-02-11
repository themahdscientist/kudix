<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use App\Layouts\SupplierLayout;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuppliersRelationManager extends RelationManager
{
    protected static string $relationship = 'suppliers';

    public function form(Form $form): Form
    {
        return $form
            ->schema(SupplierLayout::getForm());
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
                    ->icon(fn ($record) => \App\Enums\SupplierType::from($record->type)->getIcon())
                    ->color(fn ($record) => \App\Enums\SupplierType::from($record->type)->getColor())
                    ->tooltip(fn ($record) => \App\Enums\SupplierType::from($record->type)->getLabel()),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(\App\Enums\SupplierType::class),
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
