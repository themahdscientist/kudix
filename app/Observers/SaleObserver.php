<?php

namespace App\Observers;

use App\Models\Sale;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SaleObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Sale $sale): void
    {
        if (filament()->auth()->check()) {
            $sale->salesperson_id = filament()->auth()->id();
            $sale->saveQuietly();
        }
    }
}
