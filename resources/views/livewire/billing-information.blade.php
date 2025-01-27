<section>
    <x-filament::breadcrumbs :breadcrumbs="[
    route('billing.index') => 'Billing',
    'Billing information'
]" />
    <section class="lg:mt-20">
        <h3 class="pb-4 text-sm font-semibold uppercase border-b border-dark/25 dark:border-secondary">
            billing and shipping information
        </h3>
        <form wire:submit="save" class="py-4">
            {{ $this->form }}

            <div class="flex items-center gap-2 py-4">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium rounded-lg text-dark dark:text-light bg-accent dark:bg-primary">
                    Save
                </button>
                <button wire:click="cancel" type="button"
                    class="px-4 py-2 text-sm border rounded-lg border-dark/25 dark:border-light/25">
                    Cancel
                </button>
                <x-filament::loading-indicator wire:loading wire:target="save, cancel"
                    class="inline w-5 h-5 dark:text-light" role="status" aria-hidden="true" />
            </div>
        </form>

        <x-filament-actions::modals />
    </section>
</section>