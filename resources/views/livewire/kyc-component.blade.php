<x-filament-breezy::grid-section md=2 title="KYC"
    description="Regulations require these fields filled before engaging in any online transactions via this app.">
    <x-filament::card>
        <form wire:submit.prevent="verify" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit">
                    <x-filament::loading-indicator wire:loading wire:target="verify" class="w-4 h-4 text-light" role="status"
                        aria-hidden="true" />
                    Verify
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>