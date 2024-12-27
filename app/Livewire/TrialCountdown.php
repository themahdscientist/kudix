<?php

namespace App\Livewire;

use Livewire\Component;

class TrialCountdown extends Component
{
    public $trialEndsAt;

    public function mount()
    {
        $this->trialEndsAt = filament()->auth()->user()->trial_ends_at;
    }
}
