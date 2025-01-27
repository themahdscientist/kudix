<?php

namespace App\Jobs;

use App\Events\ApiFetched;
use Binkode\Paystack\Support\Miscellaneous;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class FetchPaystackCountries implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $countries = collect(Miscellaneous::listCountries()['data'])->pluck('name', 'iso_code')->toArray();

        if (! empty($countries)) {
            Cache::put('paystack-countries', $countries, now()->addYear());

            broadcast(new ApiFetched('countries', $countries));
        }
    }
}
