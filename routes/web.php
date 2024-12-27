<?php

// use App\Models\Sale;
// use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Controllers\PaystackCallback;
use App\Livewire\Pricing;
use Illuminate\Support\Facades\Route;

Route::get('welcome', function () {
    return view('welcome');
});

Route::get('', function () {
    return redirect()->route('pricing');
})->name('index');

Route::get('pricing', Pricing::class)->name('pricing');

Route::middleware('filament.auth')->group(function () {
    Route::get('/paystack/callback', PaystackCallback::class)->name('paystack.callback');
});

// ? Downloading documents in PDF formats directly.
// Route::middleware('auth')->group(function () {
//     Route::get(filament()->getCurrentPanel()->getId().'/document/{record}/invoice/download', function (Sale $record) {
//         $record = $record->load(['client.clientInfo', 'document', 'products']);
//         $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
//         $download_view = 'filament.admin.resources.document-resource.pages.download-invoice';

//         return Pdf::loadView($download_view, compact('record', 'fmt'))
//             ->setPaper(request()->query('format'), request()->query('orientation'))
//             ->stream($record->document->uuid.'.pdf');
//     })->name('invoice.download');

//     Route::get(filament()->getCurrentPanel()->getId().'/document/{record}/receipt/download', function (Sale $record) {
//         $record = $record->load(['client.clientInfo', 'document', 'products']);
//         $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
//         $download_view = 'filament.admin.resources.document-resource.pages.download-receipt';

//         return Pdf::loadView($download_view, compact('record', 'fmt'))
//             ->setPaper(request()->query('format'), request()->query('orientation'))
//             ->stream($record->document->uuid.'.pdf');
//     })->name('receipt.download');
// });
