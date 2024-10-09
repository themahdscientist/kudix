<x-filament-breezy::grid-section md=1 title="Company Information & Global Settings" description="Manage your company information and settings.">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    Update
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>