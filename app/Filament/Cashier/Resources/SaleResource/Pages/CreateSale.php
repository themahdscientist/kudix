<?php

namespace App\Filament\Cashier\Resources\SaleResource\Pages;

use App\Filament\Cashier\Resources\SaleResource;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected ?string $heading = 'POS';

    protected static string $view = 'filament.cashier.resources.sale-resource.pages.create-sale';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product')
                    ->options(fn () => Product::query()->pluck('name', 'id'))
                    ->searchable(),
            ]);
    }
}
