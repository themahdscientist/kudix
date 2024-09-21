<?php

use App\Models\Sale;
use Illuminate\Support\Facades\Route;

use function Spatie\LaravelPdf\Support\pdf;

Route::get('', function () {
    return redirect(filament()->getDefaultPanel()->getUrl());
});

Route::get('welcome', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get(filament()->getCurrentPanel()->getId().'/sales/{record}/document/invoice/download', function (Sale $record) {
        $record = $record->load(['customer', 'document', 'products']);
        $fmt = new NumberFormatter('en_NG', NumberFormatter::CURRENCY);
        $download_view = 'filament.admin.resources.document-resource.pages.download-invoice';

        return pdf()
            ->view($download_view, compact('record', 'fmt'))
            ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                $browsershot
                    ->setIncludePath('/home/themahdscientist/.nvm/versions/node/v21.7.2/bin')
                    ->newHeadless();
            })
            ->format(request()->query('format'))
            ->orientation(request()->query('orientation'))
            ->name($record->document->uuid.'.pdf');
    })->name('invoice.download');

    Route::get(filament()->getCurrentPanel()->getId().'/sales/{record}/document/receipt/download', function (Sale $record) {
        $record = $record->load(['customer', 'document', 'products']);
        $fmt = new NumberFormatter('en_NG', NumberFormatter::CURRENCY);
        $download_view = 'filament.admin.resources.document-resource.pages.download-receipt';

        return pdf()
            ->view($download_view, compact('record', 'fmt'))
            ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                $browsershot
                    ->setIncludePath('/home/themahdscientist/.nvm/versions/node/v21.7.2/bin')
                    ->newHeadless();
            })
            ->format(request()->query('format'))
            ->orientation(request()->query('orientation'))
            ->name($record->document->uuid.'.pdf');
    })->name('receipt.download');
});
