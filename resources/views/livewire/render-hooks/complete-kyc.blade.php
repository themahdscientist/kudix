
<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public bool $verified;

    public function mount()
    {
        $this->verified = filament()->auth()->user()->verified();
    }
}; ?>

<section class="container">
    @if ($verified)
    <div></div>
    @else
    <div class="kyc">
        <a class="profile-link" href="{{ route('filament.admin.pages.profile') }}">Complete KYC</a>
    </div>
    @endif
</section>