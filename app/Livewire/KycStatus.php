<?php

namespace App\Livewire;

use Livewire\Component;

class KycStatus extends Component
{
    public $setting;

    public $user;

    public function mount()
    {
        $this->user = filament()->auth()->user()->load('setting');
        $this->setting = $this->user->setting;
    }
}
