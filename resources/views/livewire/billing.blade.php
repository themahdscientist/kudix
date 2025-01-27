<div>
    <section class="lg:mt-20">
        <h3 class="pb-4 text-sm font-semibold uppercase border-b border-dark/25 dark:border-light/75">current
            subscription</h3>
        <div class="flex items-center justify-between py-4">
            <div>
                <p class="text-lg font-bold">{{ filament()->getBrandName() }} {{ $this->subscription->type }}</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $this->fmt($this->subscription->amount) }} per month</p>
                <p class="flex items-center gap-2 mt-4 text-xs dark:text-light/75">
                    @svg('heroicon-s-calendar-days', 'h-4 w-4')
                    <span>Your subscription renews on {{ $this->subscription->ends_at->format('F j, Y') }}.</span>
                </p>
            </div>
            <a wire:navigate href="{{ route('billing.update') }}"
                class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold rounded-lg bg-dark text-light dark:text-dark dark:bg-light">
                <span>
                    @svg('heroicon-s-adjustments-vertical', 'inline h-5 w-5')
                </span>
                Update subscription
            </a>
        </div>
    </section>
    <section class="lg:mt-20">
        <h3 class="pb-4 text-sm font-semibold uppercase border-b border-dark/25 dark:border-light/75">
            payment method
        </h3>
        <div class="py-4">
            <div
                class="flex items-center justify-start gap-2 transition duration-300 dark:text-light/75 text-dark/75 hover:text-dark dark:hover:text-light">
                <x-filament::loading-indicator wire:loading wire:target="manageCardsOrCancelSubscription"
                    class="inline w-4 h-4 text-dark/75 dark:text-light/75" role="status" aria-hidden="true" />
                <span wire:loading.remove wire:target="manageCardsOrCancelSubscription">
                    @svg('heroicon-s-credit-card', 'h-4 w-4')
                </span>
                <button wire:click="manageCardsOrCancelSubscription" class="text-sm font-medium">
                    Manage cards or cancel subscription
                </button>
            </div>
        </div>
    </section>
    <section class="lg:mt-20">
        <h3 class="pb-4 text-sm font-semibold uppercase border-b border-dark/25 dark:border-light/75">
            billing and shipping information
        </h3>
        <div class="py-4">
            <div
                class="flex items-center justify-start gap-2 transition duration-300 dark:text-light/75 text-dark/75 hover:text-dark dark:hover:text-light">
                @svg('heroicon-s-pencil-square', 'h-4 w-4')
                <a wire:navigate href="{{ route('billing.info') }}" class="block text-sm font-medium">
                    Update information
                </a>
            </div>
        </div>
    </section>
    <section class="lg:mt-20">
        <h3 class="pb-4 text-sm font-semibold uppercase border-b border-dark/25 dark:border-light/75">
            invoice history
        </h3>
        <livewire:billing-history lazy="on-load" />
    </section>
</div>
@script
<script>
    $wire.on('link-generated', (e) => {
        open(e[0], '_blank');
    })
</script>
@endscript