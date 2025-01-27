<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $trialEndsAt;

    public function mount()
    {
        $user = filament()->auth()->user();

        if (isset($user->user_id)) {
            $sub = User::query()->find($user->user_id)->subscription();
        } else {
            $sub = $user->subscription();
        }
        
        $trial = $sub->trial_ends_at;

        if (! is_null($sub) && ! is_null($trial)) {
            if ($trial->isFuture()) {
                $this->trialEndsAt = $trial;

                return;
            }

            $this->trialEndsAt = null;
        }
    }
}; ?>

<section class="container">
    @if (isset($trialEndsAt))
    <div class="trial">
        <div id="countdown-display" data-trial-ends-at="{{ $trialEndsAt->toIso8601String() }}">
            Loading your trial information...
        </div>
        <a href="{{ route('pricing') }}" class="group">
            Subscribe
            @svg('heroicon-s-fire', 'fire')
        </a>
    </div>
    @else
    <div></div>
    @endif
</section>