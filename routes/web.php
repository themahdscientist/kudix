<?php

// use App\Models\Sale;
// use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Controllers\PaystackCallback;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('', Livewire\Welcome::class)->name('welcome');

Route::get('pricing', Livewire\Pricing::class)->name('pricing')->middleware('prospective');

Route::middleware('filament.auth')->group(function () {
    Route::get('/paystack/callback', PaystackCallback::class)->name('paystack.callback');
    Route::prefix(filament()->getId().'/billing')->name('billing.')->group(function () {
        Route::get('', Livewire\Billing::class)->name('index');
        Route::get('customer/update', Livewire\BillingInformation::class)->name('info');
        Route::get('subscription/update', Livewire\SwapPricing::class)->name('update');
    });
});

// ? Downloading documents in PDF formats directly.
// Route::middleware('auth')->group(function () {
//     Route::get(filament()->getCurrentPanel()->getId().'/document/{record}/invoice/download', function (Sale $record) {
//         $record = $record->load(['customer.customerInfo', 'document', 'products']);
//         $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
//         $download_view = 'filament.admin.resources.document-resource.pages.download-invoice';

//         return Pdf::loadView($download_view, compact('record', 'fmt'))
//             ->setPaper(request()->query('format'), request()->query('orientation'))
//             ->stream($record->document->uuid.'.pdf');
//     })->name('invoice.download');

//     Route::get(filament()->getCurrentPanel()->getId().'/document/{record}/receipt/download', function (Sale $record) {
//         $record = $record->load(['customer.customerInfo', 'document', 'products']);
//         $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
//         $download_view = 'filament.admin.resources.document-resource.pages.download-receipt';

//         return Pdf::loadView($download_view, compact('record', 'fmt'))
//             ->setPaper(request()->query('format'), request()->query('orientation'))
//             ->stream($record->document->uuid.'.pdf');
//     })->name('receipt.download');
// });
