<?php

namespace App\Jobs;

use App\Events\ApiFetched;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchIpLocation implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $id,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $country = Http::get('https://ipinfo.io', ['token' => env('IPINFO_SECRET')])->json('country');

        if (isset($country)) {
            Cache::put("ip-location-{$this->id}", $country, now()->addMinute());

            broadcast(new ApiFetched('ip-location', $country));
        }
    }
}
