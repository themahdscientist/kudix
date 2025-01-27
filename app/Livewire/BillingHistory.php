<?php

namespace App\Livewire;

use Binkode\Paystack\Support\Invoice;
use Livewire\Component;
use NumberFormatter;

class BillingHistory extends Component
{
    public ?array $history;

    public array $res;

    public function mount()
    {
        $this->res = rescue(fn () => Invoice::list(), ['status' => false]);

        if ($this->res['status']) {
            $this->history = $this->res['data'];
        }
    }

    public function fmt($val)
    {
        return (new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY))->formatCurrency(round(abs($val) / 100), 'NGN');
    }
}
