<?php

namespace App\Filament\Admin\Resources\SaleResource\Pages;

use App\Filament\Admin\Resources\SaleResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;

class ViewInvoice extends Page
{
    use InteractsWithRecord;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record)->load(['customer', 'invoice', 'products']);
    }

    protected static string $resource = SaleResource::class;

    protected static string $view = 'filament.admin.resources.sale-resource.pages.view-invoice';

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Actions\Action::make('download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color(Color::Emerald)
                ->modalWidth(MaxWidth::ExtraSmall)
                ->modalHeading('Invoice')
                ->modalSubmitActionLabel('Download')
                ->form([
                    Forms\Components\Select::make('format')
                        ->options(\Spatie\LaravelPdf\Enums\Format::class)
                        ->default(\Spatie\LaravelPdf\Enums\Format::A4->value)
                        ->required(),
                    Forms\Components\Select::make('orientation')
                        ->options(\Spatie\LaravelPdf\Enums\Orientation::class)
                        ->default(\Spatie\LaravelPdf\Enums\Orientation::Portrait->value)
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    redirect()->route('invoice.download', ['record' => $record, 'format' => $data['format'], 'orientation' => $data['orientation']]);

                    $this->closeActionModal();
                    \Filament\Notifications\Notification::make()
                        ->title('Downloaded successfully')
                        ->icon('heroicon-o-check-circle')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('print')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(fn () => $this->js('print()')),
        ];
    }
}
